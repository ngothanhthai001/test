<?php

namespace Amasty\Payrestriction\Block\Adminhtml\Rule\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('amasty_payrestriction_rule_edit_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Rule Configuration'));
    }
}
