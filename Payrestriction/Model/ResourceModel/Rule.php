<?php

namespace Amasty\Payrestriction\Model\ResourceModel;

use Amasty\CommonRules\Model\ResourceModel\AbstractRule;

class Rule extends AbstractRule
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('am_payrestriction_rule', 'rule_id');
    }

    /**
     * Return codes of all product attributes currently used in promo rules
     *
     * @return array
     */
    public function getAttributes()
    {
        $db = $this->getConnection();
        $tbl   = $this->getTable('am_payrestriction_attribute');

        $select = $db->select()->from($tbl, new \Zend_Db_Expr('DISTINCT code'));
        return $db->fetchCol($select);
    }

    /**
     * Save product attributes currently used in conditions and actions of the rule
     */
    public function saveAttributes($id, $attributes)
    {
        $db = $this->getConnection();
        $tbl = $this->getTable('am_payrestriction_attribute');

        $db->delete($tbl, ['rule_id=?' => $id]);

        foreach ($attributes as $code) {
            $data[] = [
                'rule_id' => $id,
                'code'    => $code,
            ];
        }
        $db->insertMultiple($tbl, $data);

        return $this;
    }
}
