<?php

declare(strict_types=1);

namespace Amasty\Coupons\Model\Converter;

use Magento\SalesRule\Model\Converter\ToModel as MagentoToModel;
use Magento\SalesRule\Model\Data\Rule as RuleDataModel;
use Magento\SalesRule\Model\Rule;

class ToModel extends MagentoToModel
{
    /**
     * @param RuleDataModel $dataModel
     * @return \Magento\SalesRule\Model\Converter\ToModel|Rule
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\InputException
     */
    public function toModel(RuleDataModel $dataModel)
    {
        $ruleId = $dataModel->getRuleId();

        if ($ruleId) {
            $ruleModel = $this->ruleFactory->create()->load($ruleId);
            if (!$ruleModel->getId()) {
                throw new \Magento\Framework\Exception\NoSuchEntityException();
            }
        } else {
            $ruleModel = $this->ruleFactory->create();
        }

        $modelData = $ruleModel->getData();

        $data = $this->dataObjectProcessor->buildOutputDataArray(
            $dataModel,
            \Magento\SalesRule\Api\Data\RuleInterface::class
        );

        $mergedData = array_merge($modelData, $data);

        $validateResult = $ruleModel->validateData(new \Magento\Framework\DataObject($mergedData));
        if ($validateResult !== true) {
            $text = '';
            /** @var \Magento\Framework\Phrase $errorMessage */
            foreach ($validateResult as $errorMessage) {
                $text .= $errorMessage->getText();
                $text .= '; ';
            }
            throw new \Magento\Framework\Exception\InputException(new \Magento\Framework\Phrase($text));
        }

        $ruleModel->setData($mergedData);

        $this->mapFields($ruleModel, $dataModel);

        return $ruleModel;
    }
}