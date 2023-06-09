<?php

namespace Amasty\Payrestriction\Controller\Adminhtml\Rule;

class Activate extends \Amasty\Payrestriction\Controller\Adminhtml\Rule\AbstractMassAction
{
    protected function massAction($collection)
    {
        foreach($collection as $model)
        {
            $model->setIsActive(1);
            $model->save();
        }
        $message = __('Record(s) have been updated.');
        $this->messageManager->addSuccess($message);
    }
}
