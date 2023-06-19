<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_AdminPermissions
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AdminPermissions\Ui\DataProvider\Product\Form\Modifier;

use Magento\Backend\Model\Auth\Session;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;

/**
 * Class ProductOwner
 * @package Mageplaza\AdminPermissions\Ui\DataProvider\Product\Form\Modifier
 */
class ProductOwner extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var Session
     */
    private $authSession;

    /**
     * ProductOwner constructor.
     *
     * @param LocatorInterface $locator
     * @param Session $authSession
     */
    public function __construct(
        LocatorInterface $locator,
        Session $authSession
    ) {
        $this->locator     = $locator;
        $this->authSession = $authSession;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $modelId = $this->locator->getProduct()->getId();
        $user    = $this->authSession->getUser();
        if ($user && !isset($data[$modelId]['product']['mp_product_owner'])) {
            $data[$modelId]['product']['mp_product_owner'] = $user->getId();
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        return $meta;
    }
}
