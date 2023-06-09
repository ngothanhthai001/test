<?php

namespace Amasty\PromoReports\Plugin\Reports\Model\ResourceModel\Refresh;

use Amasty\PromoReports\Model\ResourceModel\Report;
use Magento\Framework\DataObject;
use Magento\Framework\DataObject\Factory as DataObjectFactory;
use Magento\Reports\Model\FlagFactory;
use Magento\Reports\Model\ResourceModel\Refresh\Collection;

/**
 * Add to Magento Refresh Statistics Page our 'Free Gift' Record
 */
class CollectionPlugin
{
    /**
     * @var FlagFactory
     */
    private $reportsFlagFactory;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var DataObject
     */
    private $dataObject;

    public function __construct(FlagFactory $reportsFlagFactory, DataObjectFactory $dataObjectFactory)
    {
        $this->reportsFlagFactory = $reportsFlagFactory;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Add 'Free Gift' report record and ignore if loaded
     *
     * @param Collection $subject
     * @param Collection $result
     * @param bool $printQuery
     * @param bool $logQuery
     * @return Collection
     */
    public function afterLoadData(Collection $subject, Collection $result, $printQuery = false, $logQuery = false)
    {
        if (!$subject->getFlag('amastyPromoReportFlag')) {
            $result->addItem($this->getDataObject());
            $subject->setFlag('amastyPromoReportFlag', 1);
        }

        return $result;
    }

    /**
     * @return DataObject
     */
    public function getDataObject(): DataObject
    {
        if (!empty((array)$this->dataObject)) {
            return $this->dataObject;
        }

        $data = [
            'id' => 'amastyPromoReport',
            'report' => __('Amasty Free Gift'),
            'comment' => __('Amasty Free Gift Report'),
            'updated_at' => $this->getUpdatedAt(Report::REPORT_PROMO_FLAG_CODE)
        ];

        return $this->createDataObject($data);
    }

    /**
     * @param array $data
     * @return DataObject
     */
    public function createDataObject(array $data = []): DataObject
    {
        return $this->dataObject = $this->dataObjectFactory->create($data);
    }

    /**
     * @param string $reportCode
     * @return string
     */
    protected function getUpdatedAt($reportCode)
    {
        $flag = $this->reportsFlagFactory->create()->setReportFlagCode($reportCode)->loadSelf();

        return $flag->hasData() ? $flag->getLastUpdate() : '';
    }
}
