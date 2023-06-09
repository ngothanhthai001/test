<?php

namespace Amasty\PromoReports\Api;

use Amasty\PromoReports\Api\Data\ReportInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @api
 */
interface ReportRepositoryInterface
{
    /**
     * Save
     *
     * @param ReportInterface $discount
     *
     * @return ReportInterface
     */
    public function save(ReportInterface $discount): ReportInterface;

    /**
     * Get by id
     *
     * @param int $entityId
     *
     * @return ReportInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $entityId): ReportInterface;

    /**
     * Delete
     *
     * @param ReportInterface $entity
     *
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function delete(ReportInterface $entity): bool;

    /**
     * Delete by id
     *
     * @param int $entityId
     *
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function deleteById(int $entityId): bool;
}
