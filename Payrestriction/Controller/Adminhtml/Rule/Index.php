<?php
namespace Amasty\Payrestriction\Controller\Adminhtml\Rule;


class Index extends \Amasty\Payrestriction\Controller\Adminhtml\Rule
{

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Amasty_Payrestriction::sales_restiction');
        $resultPage->addBreadcrumb(__('Rules'), __('Rules'));
        $resultPage->getConfig()->getTitle()->prepend(__('Payment Restrictions'));

        return $resultPage;
    }
}
