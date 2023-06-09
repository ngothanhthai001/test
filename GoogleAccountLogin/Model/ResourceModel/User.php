<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GoogleAccountLogin
 */


namespace Amasty\GoogleAccountLogin\Model\ResourceModel;

class User extends \Magento\User\Model\ResourceModel\User
{
    /**
     * @param $email
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByEmail($email)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from($this->getMainTable())->where('email=:email');

        return $connection->fetchRow($select, ['email' => $email]);
    }
}
