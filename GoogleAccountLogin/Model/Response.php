<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Google Account Login for Magento 2
 */


namespace Amasty\GoogleAccountLogin\Model;

use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Response
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(ConfigProvider $configProvider, LoggerInterface $logger)
    {
        $this->configProvider = $configProvider;
        $this->logger = $logger;
    }

    /**
     * @param $rawResponse
     * @return array|bool
     */
    public function getUserData($rawResponse)
    {
        $userData = [];
        try {
            $samlSettings = new \OneLogin\Saml2\Settings($this->configProvider->getSettings());
            $samlResponse = new \OneLogin\Saml2\Response($samlSettings, $rawResponse);

            if ($samlResponse->isValid()) {
                $userData['emailAddress'] = $samlResponse->getNameIdData()['Value'] ?? '';
                foreach ($samlResponse->getAttributes() as $name => $values) {
                    $userData[$name] = $values[0] ?? '';
                }
            } else {
                $this->throwException();

            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }

        return $userData;
    }

    /**
     * @throws LocalizedException
     */
    protected function throwException()
    {
        throw new LocalizedException(__('Invalid SAML response.'));
    }
}
