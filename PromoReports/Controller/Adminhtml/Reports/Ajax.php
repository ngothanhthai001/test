<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Reports for Free Gift (Add-On) for Magento 2
 */

namespace Amasty\PromoReports\Controller\Adminhtml\Reports;

use Amasty\PromoReports\Api\Data\ReportInterface;
use Amasty\PromoReports\Model\ReportBuilder;
use Amasty\PromoReports\Model\ReportManagement;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Webapi\Response;
use Psr\Log\LoggerInterface;

class Ajax extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Amasty_PromoReports::promo_reports';

    public const DIGITAL_KEY = 'digital_statistics';
    public const GRAPHIC_KEY = 'graphic_statistics';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ReportManagement
     */
    private $reportManagement;

    /**
     * @var ReportBuilder
     */
    private $reportBuilder;

    public function __construct(
        Action\Context $context,
        LoggerInterface $logger,
        ReportManagement $reportManagement,
        ReportBuilder $reportBuilder
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->reportManagement = $reportManagement;
        $this->reportBuilder = $reportBuilder;
    }

    /**
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $store = $this->getRequest()->getParam('store');
        $customerGroup = $this->getRequest()->getParam('customer_group');
        $dateRange = $this->getRequest()->getParam('date_range');
        $dateFrom = $this->getRequest()->getParam('date_from');
        $dateTo = $this->getRequest()->getParam('date_to');

        $this->reportBuilder->addStoreId($store)
            ->addCustomerGroupId($customerGroup)
            ->addDate($dateRange, $dateFrom, $dateTo);

        try {
            $response['type'] = Response::MESSAGE_TYPE_SUCCESS;
            $response['data']['checkDataFields'] = $this->getCheckDataFields();
            $response['data']['statisticsData'] = $this->reportBuilder->getDigitalStatistics();
            $response['data']['averageCheckData'] = $this->reportBuilder->getGraphStatistics();
        } catch (LocalizedException $exception) {
            $response['type'] = Response::MESSAGE_TYPE_WARNING;
            $response['message'] = $exception->getMessage();
        } catch (\Exception $exception) {
            $response['type'] = Response::MESSAGE_TYPE_ERROR;
            $response['message'] = __('Something went wrong. Please try again or check your Magento log file.');

            $this->logger->error($exception->getMessage());
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($response);
    }

    /**
     * @return array
     */
    private function getCheckDataFields(): array
    {
        return [
            "category" => ReportInterface::PERIOD,
            "dataSeries" => [
                [
                    "name" => ReportInterface::AVG_WITH_PROMO,
                    "label" => __("Average Order Value (with Promo)")
                ],
                [
                    "name" => ReportInterface::AVG_WITHOUT_PROMO,
                    "label" => __("Average Order Value (without Promo)")
                ]
            ]
        ];
    }
}
