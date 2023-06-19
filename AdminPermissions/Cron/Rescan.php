<?php

namespace Mageplaza\AdminPermissions\Cron;

use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Mageplaza\AdminPermissions\Helper\Data;

/**
 * Class Rescan
 * @package Mageplaza\AdminPermissions\Cron
 */
class Rescan
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * Rescan constructor.
     *
     * @param Data $helperData
     */
    public function __construct(Data $helperData)
    {
        $this->helperData = $helperData;
    }

    /**
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function execute()
    {
        $this->helperData->aggregateCustomTable();
    }
}
