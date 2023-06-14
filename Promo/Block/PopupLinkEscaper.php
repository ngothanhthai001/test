<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Block;

use Magento\Framework\Escaper;
use Psr\Log\LoggerInterface;

/**
 * We need not escape 'data-role' attribute to have possibility to open popup.
 */
class PopupLinkEscaper
{
    /**
     * @var int
     */
    private $htmlSpecialCharsFlag = ENT_QUOTES | ENT_SUBSTITUTE;

    /**
     * @var string[]
     */
    private $allowedAttributes = ['id', 'class', 'href', 'title', 'style', 'data-role'];

    /**
     * @var string[]
     */
    private $notAllowedTags = ['script', 'img', 'embed', 'iframe', 'video', 'source', 'object', 'audio'];

    /**
     * @var array[]
     */
    private $notAllowedAttributes = ['a' => ['style']];

    /**
     * @var string[]
     */
    private $escapeAsUrlAttributes = ['href'];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Escaper
     */
    private $escaper;

    public function __construct(
        Escaper $escaper,
        LoggerInterface $logger
    ) {
        $this->escaper = $escaper;
        $this->logger = $logger;
    }

    /**
     * @param string|array $data
     * @param array|null $allowedTags
     * @return string|array
     */
    public function escapeHtml($data, $allowedTags = null)
    {
        if (!is_array($data)) {
            $data = (string)$data;
        }

        if (is_array($data)) {
            $result = [];
            foreach ($data as $item) {
                $result[] = $this->escapeHtml($item, $allowedTags);
            }
        } elseif (!empty($data)) {
            if (is_array($allowedTags) && !empty($allowedTags)) {
                $allowedTags = $this->filterProhibitedTags($allowedTags);
                $wrapperElementId = uniqid('', true);
                $domDocument = new \DOMDocument('1.0', 'UTF-8');
                set_error_handler(
                    function ($errorNumber, $errorString) {
                        // phpcs:ignore Magento2.Exceptions.DirectThrow
                        throw new \InvalidArgumentException($errorString, $errorNumber);
                    }
                );
                $data = $this->prepareUnescapedCharacters($data);
                $convmap = [0x80, 0x10FFFF, 0, 0x1FFFFF];
                $string = mb_encode_numericentity(
                    $data,
                    $convmap,
                    'UTF-8'
                );
                try {
                    $domDocument->loadHTML(
                        '<html><body id="' . $wrapperElementId . '">' . $string . '</body></html>'
                    );
                } catch (\Exception $e) {
                    restore_error_handler();
                    $this->logger->critical($e);
                }
                restore_error_handler();

                $this->removeComments($domDocument);
                $this->removeNotAllowedTags($domDocument, $allowedTags);
                $this->removeNotAllowedAttributes($domDocument);
                $this->escapeText($domDocument);
                $this->escapeAttributeValues($domDocument);

                $result = mb_decode_numericentity(
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                    html_entity_decode(
                        $domDocument->saveHTML(),
                        ENT_QUOTES|ENT_SUBSTITUTE,
                        'UTF-8'
                    ),
                    $convmap,
                    'UTF-8'
                );
                preg_match('/<body id="' . $wrapperElementId . '">(.+)<\/body><\/html>$/si', $result, $matches);
                return !empty($matches) ? $matches[1] : '';
            } else {
                // phpcs:ignore Magento2.Functions.DiscouragedFunction.DiscouragedWithAlternative
                $result = htmlspecialchars($data, $this->htmlSpecialCharsFlag, 'UTF-8', false);
            }
        } else {
            $result = $data;
        }
        return $result;
    }

    private function filterProhibitedTags(array $allowedTags): array
    {
        $notAllowedTags = array_intersect(
            array_map('strtolower', $allowedTags),
            $this->notAllowedTags
        );

        if (!empty($notAllowedTags)) {
            $this->logger->critical(
                'The following tag(s) are not allowed: ' . implode(', ', $notAllowedTags)
            );
            $allowedTags = array_diff($allowedTags, $this->notAllowedTags);
        }

        return $allowedTags;
    }

    private function prepareUnescapedCharacters(string $data): ?string
    {
        $patterns = ['/\&/u'];
        $replacements = ['&amp;'];
        return \preg_replace($patterns, $replacements, $data);
    }

    private function removeComments(\DOMDocument $domDocument)
    {
        $xpath = new \DOMXPath($domDocument);
        $nodes = $xpath->query('//comment()');
        foreach ($nodes as $node) {
            $node->parentNode->removeChild($node);
        }
    }

    private function removeNotAllowedTags(\DOMDocument $domDocument, array $allowedTags): void
    {
        $xpath = new \DOMXPath($domDocument);
        $nodes = $xpath->query(
            '//node()[name() != \''
            . implode('\' and name() != \'', array_merge($allowedTags, ['html', 'body']))
            . '\']'
        );
        foreach ($nodes as $node) {
            if ($node->nodeName != '#text') {
                $node->parentNode->replaceChild($domDocument->createTextNode($node->textContent), $node);
            }
        }
    }

    private function removeNotAllowedAttributes(\DOMDocument $domDocument): void
    {
        $xpath = new \DOMXPath($domDocument);
        $nodes = $xpath->query(
            '//@*[name() != \'' . implode('\' and name() != \'', $this->allowedAttributes) . '\']'
        );
        foreach ($nodes as $node) {
            $node->parentNode->removeAttribute($node->nodeName);
        }

        foreach ($this->notAllowedAttributes as $tag => $attributes) {
            $nodes = $xpath->query(
                '//@*[name() =\'' . implode('\' or name() = \'', $attributes) . '\']'
                . '[parent::node()[name() = \'' . $tag . '\']]'
            );
            foreach ($nodes as $node) {
                $node->parentNode->removeAttribute($node->nodeName);
            }
        }
    }

    private function escapeText(\DOMDocument $domDocument): void
    {
        $xpath = new \DOMXPath($domDocument);
        $nodes = $xpath->query('//text()');
        foreach ($nodes as $node) {
            $node->textContent = $this->escapeHtml($node->textContent);
        }
    }

    private function escapeAttributeValues(\DOMDocument $domDocument): void
    {
        $xpath = new \DOMXPath($domDocument);
        $nodes = $xpath->query('//@*');
        foreach ($nodes as $node) {
            $value = $this->escapeAttributeValue(
                $node->nodeName,
                $node->parentNode->getAttribute($node->nodeName)
            );
            $node->parentNode->setAttribute($node->nodeName, $value);
        }
    }

    private function escapeAttributeValue(string $name, string $value): string
    {
        return in_array($name, $this->escapeAsUrlAttributes)
            ? $this->escaper->escapeUrl($value)
            : $this->escapeHtml($value);
    }
}
