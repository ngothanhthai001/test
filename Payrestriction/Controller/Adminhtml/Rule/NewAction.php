<?php

namespace Amasty\Payrestriction\Controller\Adminhtml\Rule;

use Magento\Backend\App\Action;
use Magento\Catalog\Controller\Adminhtml\Product;

class NewAction extends \Amasty\Payrestriction\Controller\Adminhtml\Rule
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
