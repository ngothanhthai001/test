<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Google Account Login for Magento 2
 */

namespace Amasty\GoogleAccountLogin\Block;

class LoginButton extends \Magento\Backend\Block\Template
{
    /**
     * @return string
     */
    public function getOneLoginUrl()
    {
        try {
            $samlSettings = new \OneLogin\Saml2\Settings($this->getData('configProvider')->getSettings());
            $idpData = $samlSettings->getIdPData();
            $idpSSO = $idpData['singleSignOnService']['url'] ?? '';
            if ($idpSSO) {
                $authnRequest = new \OneLogin\Saml2\AuthnRequest($samlSettings);
                $parameters['SAMLRequest'] = $authnRequest->getRequest();
                $idpSSO = \OneLogin\Saml2\Utils::redirect($idpSSO, $parameters, true);
            }
        } catch (\Exception $e) {
            $idpSSO = '';
            $this->_logger->error($e->getMessage());
        }

        return $idpSSO;
    }
}
