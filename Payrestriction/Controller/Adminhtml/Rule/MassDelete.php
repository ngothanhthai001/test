<?php

namespace Amasty\Payrestriction\Controller\Adminhtml\Rule;

class MassDelete extends \Amasty\Payrestriction\Controller\Adminhtml\Rule\AbstractMassAction
{
    protected function massAction($collection)
    {
        foreach($collection as $model)
        {
            $model->delete();
        }
        $this->messageManager->addSuccess(__('Record(s) were successfully deleted'));
    }
}
