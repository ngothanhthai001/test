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

namespace Mageplaza\AdminPermissions\Plugin\Catalog\Controller\Adminhtml\Product;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Ui\Component\MassAction\Filter;
use Mageplaza\AdminPermissions\Helper\Data;

/**
 * Class MassDelete
 * @package Mageplaza\AdminPermissions\Plugin\Catalog\Controller\Adminhtml\Product
 */
class MassDelete
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * MassDelete constructor.
     *
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param ManagerInterface $messageManager
     * @param ResultFactory $resultFactory
     * @param Data $helperData
     */
    public function __construct(
        Filter $filter,
        CollectionFactory $collectionFactory,
        ManagerInterface $messageManager,
        ResultFactory $resultFactory,
        Data $helperData
    ) {
        $this->filter            = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->messageManager    = $messageManager;
        $this->resultFactory     = $resultFactory;
        $this->helperData        = $helperData;
    }

    /**
     * @param \Magento\Catalog\Controller\Adminhtml\Product\MassDelete $massDelete
     * @param callable $proceed
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function aroundExecute(
        \Magento\Catalog\Controller\Adminhtml\Product\MassDelete $massDelete,
        callable $proceed
    ) {
        if (!$this->helperData->isPermissionEnabled()) {
            return $proceed();
        }

        $collection     = $this->filter->getCollection($this->collectionFactory->create());
        $productDeleted = 0;
        /** @var Product $product */
        foreach ($collection->getItems() as $product) {
            try {
                try {
                    $product->delete();
                    $productDeleted++;
                } catch (Exception $e) {
                    $this->messageManager->addErrorMessage(
                        __('Something went wrong while deleting product %1. %2', $product->getName(), $e->getMessage())
                    );
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        $this->messageManager->addSuccessMessage(
            __('A total of %1 record(s) have been deleted.', $productDeleted)
        );

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('catalog/*/index');
    }
}
