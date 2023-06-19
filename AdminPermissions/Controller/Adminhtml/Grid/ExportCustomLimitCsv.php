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
 * @package     Mageplaza_LoyaltyProgram
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AdminPermissions\Controller\Adminhtml\Grid;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Mageplaza\AdminPermissions\Block\Adminhtml\Role\Tab\Renderer\Custom;

/**
 * Class ExportCustomLimitCsv
 * @package Mageplaza\AdminPermissions\Controller\Adminhtml\Grid
 */
class ExportCustomLimitCsv extends Action
{
    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * ExportCustomLimitCsv constructor.
     *
     * @param Context $context
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory
    ) {
        $this->fileFactory = $fileFactory;

        parent::__construct($context);
    }

    /**
     * Export customer as excel csv file
     *
     * @return ResponseInterface|ResultInterface|null
     * @throws Exception
     */
    public function execute()
    {
        $fileName = 'export.csv';

        $content = $this->_view->getLayout()->createBlock(Custom::class)->getCsvFile();

        return $this->fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
    }
}
