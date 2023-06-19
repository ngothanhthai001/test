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
 * @package     Mageplaza_AdminPermissions
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\AdminPermissions\Console\Command;

use Exception;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\ModuleResource;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class Uninstall
 * @package Mageplaza\AdminPermissions\Console\Command
 */
class Uninstall extends Command
{
    /**
     * @var ModuleResource
     */
    protected $moduleResource;

    /**
     * @var AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var State
     */
    protected $state;

    /**
     * @var array
     */
    protected $eavAttrCodes = [
        'mp_product_owner',
    ];

    /**
     * Uninstall constructor.
     *
     * @param ModuleResource $moduleResource
     * @param AttributeFactory $attributeFactory
     * @param State $state
     * @param null $name
     */
    public function __construct(
        ModuleResource $moduleResource,
        AttributeFactory $attributeFactory,
        State $state,
        $name = null
    ) {
        $this->moduleResource   = $moduleResource;
        $this->attributeFactory = $attributeFactory;
        $this->state            = $state;

        parent::__construct($name);
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('mageplaza-adminpermissions:uninstall')
            ->setDescription('Prepare for remove Mageplaza_AdminPermissions module');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     * @throws LocalizedException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode('adminhtml');
        try {
            $this->attributeFactory->create()->getCollection()->addFieldToFilter('entity_type_id', 4)
                ->addFieldToFilter('attribute_code', ['in' => $this->eavAttrCodes])->walk('delete');
            $this->moduleResource->getConnection()
                ->delete($this->moduleResource->getMainTable(), "module='Mageplaza_AdminPermissions'");
            $output->writeln('<info>Prepare remove Mageplaza_AdminPermissions module successfully</info>');
        } catch (Exception $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
        }
    }
}
