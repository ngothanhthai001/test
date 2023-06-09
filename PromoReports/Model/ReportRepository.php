<?php

namespace Amasty\PromoReports\Model;

use Amasty\PromoReports\Api\Data\ReportInterface;
use Amasty\PromoReports\Api\ReportRepositoryInterface;
use Amasty\PromoReports\Model\ResourceModel\Report as ReportResource;
use Magento\Framework\Config\Dom\ValidationException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;

class ReportRepository implements ReportRepositoryInterface
{
    /**
     * @var ReportResource
     */
    private $reportResource;

    /**
     * @var ReportFactory
     */
    private $reportModelFactory;

    public function __construct(
        ReportResource $reportResource,
        ReportFactory $reportModelFactory
    ) {
        $this->reportResource = $reportResource;
        $this->reportModelFactory = $reportModelFactory;
    }

    /**
     * @param ReportInterface $reportModel
     * @return ReportInterface
     * @throws CouldNotSaveException
     */
    public function save(ReportInterface $reportModel): ReportInterface
    {
        try {
            $this->reportResource->save($reportModel);
        } catch (ValidationException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to save model %1', $reportModel->getId()));
        }

        return $reportModel;
    }

    /**
     * @param int $entityId
     * @return ReportInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $entityId): ReportInterface
    {
        /** @var Report $reportModel */
        $reportModel = $this->reportModelFactory->create();
        $this->reportResource->load($reportModel, $entityId);

        if (!$reportModel->getId()) {
            throw new NoSuchEntityException(__('Entity with specified ID "%1" not found.', $entityId));
        }

        return $reportModel;
    }

    /**
     * @param ReportInterface $reportModel
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(ReportInterface $reportModel): bool
    {
        try {
            $this->reportResource->delete($reportModel);
        } catch (ValidationException $e) {
            throw new CouldNotDeleteException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Unable to remove entity with ID%', $reportModel->getId()));
        }

        return true;
    }

    /**
     * @param int $entityId
     * @return bool
     */
    public function deleteById(int $entityId): bool
    {
        $reportModel = $this->get($entityId);
        $this->delete($reportModel);

        return true;
    }
}
