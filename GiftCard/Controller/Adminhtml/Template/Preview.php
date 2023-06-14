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
 * @package     Mageplaza_GiftCard
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\GiftCard\Controller\Adminhtml\Template;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\Layout;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\GiftCard\Controller\Adminhtml\Template;
use Mageplaza\GiftCard\Helper\Template as TemplateHelper;
use Mageplaza\GiftCard\Model\TemplateFactory;
use Spipu\Html2Pdf\Exception\Html2PdfException;

/**
 * Class Preview
 * @package Mageplaza\GiftCard\Controller\Adminhtml\Template
 */
class Preview extends Template
{
    /**
     * @var TemplateHelper
     */
    private $templateHelper;

    /**
     * Preview constructor.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param TemplateFactory $templateFactory
     * @param TemplateHelper $templateHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        TemplateFactory $templateFactory,
        TemplateHelper $templateHelper
    ) {
        $this->templateHelper = $templateHelper;

        parent::__construct($context, $resultPageFactory, $templateFactory);
    }

    /**
     * @return ResponseInterface|ResultInterface|Layout
     * @throws NoSuchEntityException
     * @throws Html2PdfException
     */
    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $data = (array)$this->getRequest()->getPost();

            $fileUrl = $this->templateHelper->createPreview($data);

            $preview = '<iframe src="' . $fileUrl . '" style="width: 70%; height: 500px; margin-left: 15%"></iframe>';

            $result->setData([
                'preview' => $preview
            ]);

            return $result;
        }

        return $this->_redirect('noroute');
    }
}
