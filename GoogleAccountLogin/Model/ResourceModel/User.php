<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Google Account Login for Magento 2
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
