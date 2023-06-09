<?php

namespace Amasty\PromoReports\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Install Script
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var Operation\AddDailyReports
     */
    private $addDailyReports;

    public function __construct(
        Operation\AddDailyReports $addDailyReports
    ) {
        $this->addDailyReports = $addDailyReports;
    }

    public function install(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->startSetup();

        $this->addDailyReports->execute($installer);

        $installer->endSetup();
    }
}
