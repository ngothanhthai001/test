<?php

namespace Amasty\Payrestriction\Ui\Component\Listing\Column;

class Status implements \Magento\Framework\Data\OptionSourceInterface
{
    protected $options;
    protected $_statusList;

    public function __construct(\Amasty\Payrestriction\Model\System\Config\Status $statusList)
    {
        $this->_statusList = $statusList;
    }

    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = array();
            foreach($this->_statusList->toOptionArray() as $value => $label){
                $this->options[] = [
                    'value' => $value,
                    'label' => $label
                ];
            }
        }

        return $this->options;
    }
}
