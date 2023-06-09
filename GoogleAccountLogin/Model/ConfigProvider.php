<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Google Account Login for Magento 2
 */


namespace Amasty\GoogleAccountLogin\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class ConfigProvider extends \Amasty\Base\Model\ConfigProviderAbstract implements ArgumentInterface
{
    public const NAME_ID_FORMAT = 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent';

    /**
     * @var string
     */
    protected $pathPrefix = 'am_google_account_login/';

    /**
     * @var UrlInterface
     */
    private $url;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        UrlInterface $url,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        parent::__construct($scopeConfig);
        $this->url = $url;
        $this->encryptor = $encryptor;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isSetFlag('general/enabled');
    }

    /**
     * @return string
     */
    public function getTargetUrl()
    {
        return $this->getValue('general/target_url');
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        return $this->getValue('general/entity_id');
    }

    /**
     * @return string
     */
    public function getCertificate()
    {
        $key = $this->getValue('general/certificate');
        if ($key) {
            $key = $this->encryptor->decrypt($key);
        }

        return $key;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        $idpUrl = $this->getTargetUrl();
        $entityId = $this->getEntityId();
        $certificate = $this->getCertificate();

        $settings = [];
        if ($idpUrl && $entityId && $certificate) {
            $issuer = $this->url->getBaseUrl();
            $settings = [
                'strict' => false,
                'debug' => false,
                'sp' => [
                    'entityId' => $issuer,
                    'assertionConsumerService' => [
                        'url' => $this->url->getRouteUrl('adminhtml'),
                    ],
                    'NameIDFormat' => self::NAME_ID_FORMAT,
                ],
                'idp' => [
                    'entityId' => $entityId,
                    'singleSignOnService' => [
                        'url' => $idpUrl,
                    ],
                    'singleLogoutService' => [
                        'url' => $idpUrl,
                    ],
                    'x509cert' => $certificate,
                ]
            ];
        }

        return $settings;
    }
}
