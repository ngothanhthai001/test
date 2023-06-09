<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GoogleAccountLogin
 */


namespace Amasty\GoogleAccountLogin\Model\Config\Backend;

use Magento\Framework\App\Config\Data\ProcessorInterface;
use Magento\Framework\Exception\LocalizedException;

class Social extends \Magento\Framework\App\Config\Value implements ProcessorInterface
{
    /**
     * @param string $value
     *
     * @return string
     */
    public function processValue($value)
    {
        return $value;
    }

    /**
     * @return \Magento\Framework\App\Config\Value
     * @throws LocalizedException
     */
    public function save()
    {
        if ($this->getValue() == '1' && !class_exists('OneLogin\Saml2\Auth')) {
            throw new LocalizedException(
                __('Additional Google Account Login package is not installed. '
                    . 'Please, run the following command in the SSH: composer require onelogin/php-saml')
            );
        }

        return parent::save();
    }
}
