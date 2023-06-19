<?php


namespace Mageplaza\BannerSlider\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 * @package Mageplaza\Productslider\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $connection = $setup->getConnection();

        if (version_compare($context->getVersion(), '2.0.2', '<')) {
            $connection->addColumn(
                $setup->getTable('mageplaza_bannerslider_slider'),
                'url_location',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'length' => '255',
                    'comment' => 'url location',
                    'after' => 'priority'
                ]
            );
            $connection->addColumn(
                $setup->getTable('mageplaza_bannerslider_slider'),
                'display_on',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                    'nullable' => false,
                    'default' => 0,
                    'comment' => 'display on',
                    'after' => 'url_location'
                ]
            );
        }
        $setup->endSetup();
    }
}
