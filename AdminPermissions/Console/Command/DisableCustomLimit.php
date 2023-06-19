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
use Mageplaza\AdminPermissions\Model\AdminPermissions as AdminPermissionsModel;
use Mageplaza\AdminPermissions\Model\ResourceModel\AdminPermissions;
use Mageplaza\AdminPermissions\Model\ResourceModel\AdminPermissions\CollectionFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DisableCustomLimit
 * @package Mageplaza\AdminPermissions\Console\Command
 */
class DisableCustomLimit extends Command
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var AdminPermissions
     */
    private $apResource;

    /**
     * DisableCustomLimit constructor.
     *
     * @param CollectionFactory $collectionFactory
     * @param AdminPermissions $apResource
     * @param null $name
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        AdminPermissions $apResource,
        $name = null
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->apResource        = $apResource;

        parent::__construct($name);
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setName('mageplaza-adminpermissions:custom-limit:disabled')
            ->setDescription('Disable Mageplaza_AdminPermissions custom limit action');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $collection = $this->collectionFactory->create();
        /** @var AdminPermissionsModel $item */
        foreach ($collection as $item) {
            try {
                $item->setMpCustomEnabled(0);
                $this->apResource->save($item);
            } catch (Exception $e) {
                $output->writeln("<error>{$e->getMessage()}</error>");
            }
        }
        $output->writeln('<info>You have disabled all custom limit action successfully</info>');
    }
}
