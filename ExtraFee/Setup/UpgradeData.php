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
 * @package     Mageplaza_ExtraFee
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ExtraFee\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Quote\Setup\QuoteSetup;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetup;
use Magento\Sales\Setup\SalesSetupFactory;
use Mageplaza\ExtraFee\Model\ResourceModel\Rule\CollectionFactory as RuleCollection;

/**
 * Class UpgradeData
 * @package Mageplaza\ExtraFee\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var SalesSetupFactory
     */
    protected $salesSetupFactory;

    /**
     * @var QuoteSetupFactory
     */
    protected $quoteSetupFactory;

    /**
     * @var RuleCollection
     */
    protected $ruleCollection;

    /**
     * UpgradeData constructor.
     *
     * @param SalesSetupFactory $salesSetupFactory
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param RuleCollection $ruleCollection
     */
    public function __construct(
        SalesSetupFactory $salesSetupFactory,
        QuoteSetupFactory $quoteSetupFactory,
        RuleCollection $ruleCollection
    ) {
        $this->salesSetupFactory = $salesSetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->ruleCollection    = $ruleCollection;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** @var SalesSetup $salesInstaller */
        $salesInstaller = $this->salesSetupFactory->create(['resourceName' => 'sales_setup', 'setup' => $setup]);

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            $data = [
                ['table' => 'order_item', 'column' => 'mp_extra_fee'],
                ['table' => 'invoice_item', 'column' => 'mp_extra_fee'],
                ['table' => 'creditmemo_item', 'column' => 'mp_extra_fee'],
            ];

            foreach ($data as $item) {
                $salesInstaller->addAttribute(
                    $item['table'],
                    $item['column'],
                    ['type' => Table::TYPE_TEXT, 'visible' => false]
                );
            }
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            /** @var QuoteSetup $quoteInstaller */
            $quoteInstaller = $this->quoteSetupFactory->create(['resourceName' => 'quote_setup', 'setup' => $setup]);
            $quoteInstaller->addAttribute(
                'quote_address',
                'mp_extra_fee',
                ['type' => Table::TYPE_TEXT, 'visible' => false]
            );
        }

        if (version_compare($context->getVersion(), '1.0.3', '<')) {
            $updateData = [];
            $ruleCollection = $this->ruleCollection->create();

            foreach ($ruleCollection as $rule) {
                $ruleData = $rule->getData();
                $ruleData['from_date'] = $ruleData['created_at'];
                $updateData[] = $ruleData;
            }

            if (!empty($updateData)) {
                $setup->getConnection()->insertOnDuplicate(
                    $setup->getTable('mageplaza_extrafee_rule'),
                    $updateData
                );
            }
        }

        $setup->endSetup();
    }
}
