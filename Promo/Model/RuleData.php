<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Model\RuleRepository;

class RuleData
{
    /**
     * @var RuleRepository
     */
    private $ruleRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var MetadataPool
     */
    private $metadata;

    public function __construct(
        RuleRepository $ruleRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        MetadataPool $metadata
    ) {
        $this->ruleRepository = $ruleRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->metadata = $metadata;
    }

    /**
     * @throws NoSuchEntityException|LocalizedException
     * @throws \Exception
     */
    public function getRuleByLinkId(string $linkId): RuleInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            $this->metadata->getMetadata(RuleInterface::class)->getLinkField(),
            $linkId
        )->create();

        $ruleList = $this->ruleRepository->getList($searchCriteria);

        if ($ruleList->getTotalCount()) {
            return current($ruleList->getItems());
        }

        throw new NoSuchEntityException(__('Rule with specified ID "%1" not found.', $linkId));
    }
}
