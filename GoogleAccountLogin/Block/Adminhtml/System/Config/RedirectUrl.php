<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Google Account Login for Magento 2
 */


namespace Amasty\GoogleAccountLogin\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\UrlInterface;

class RedirectUrl extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var UrlInterface
     */
    private $url;

    public function __construct(
        Context $context,
        UrlInterface $url,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->url = $url;
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        $elementId   = explode('_', $element->getHtmlId());
        $redirectUrl = $this->url->getRouteUrl('adminhtml');
        $html = $this->getFieldTemplate($element, $elementId, $redirectUrl);

        return $html;
    }

    /**
     * @param $element
     * @param $elementId
     * @param $redirectUrl
     * @return string
     */
    private function getFieldTemplate($element, $elementId, $redirectUrl)
    {
        $html = '<input style="opacity:1;" readonly id="%s" class="input-text admin__control-text"
                        value="%s" onclick="this.select()" type="text">';

        return sprintf($html, $element->getHtmlId(), $redirectUrl);
    }
}
