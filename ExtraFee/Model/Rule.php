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
 * @package     Mageplaza_ExtraFee
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\ExtraFee\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Rule\Model\AbstractModel as RuleAbstractModel;
use Magento\Rule\Model\Action\Collection;
use Magento\Rule\Model\Condition\Combine;
use Magento\SalesRule\Model\Rule as AbstractModel;
use Magento\SalesRule\Model\Rule\Condition\Product\CombineFactory;
use Mageplaza\ExtraFee\Model\Rule\Condition\CombineFactory as ExtraFeeCombine;
use Mageplaza\ExtraFee\Model\ResourceModel\Rule as RuleResource;

/**
 * Class Rule
 * @package Mageplaza\ExtraFee\Model
 * @method getOptions()
 * @method getFeeTax()
 * @method getRefundable()
 * @method getArea()
 * @method getApplyType()
 * @method getStopFurtherProcessing()
 * @method getStatus()
 * @method getStoreIds()
 * @method getCustomerGroups()
 * @method setType($feeType)
 * @method getFeeType()
 * @method getLabels()
 * @method setOptions($jsonEncode)
 */
class Rule extends RuleAbstractModel
{
    /**
     * Cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'mageplaza_extrafee_rule';

    /**
     * Cache tag
     *
     * @var string
     */
    protected $_cacheTag = 'mageplaza_extrafee_rule';

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_extrafee_rule';

    /**
     * @var CombineFactory
     */
    protected $condProdCombineF;

    /**
     * @var ExtraFeeCombine
     */
    protected $condCombineFactory;

    /**
     * Initialize resource model
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param TimezoneInterface $localeDate
     * @param CombineFactory $condProdCombineF
     * @param ExtraFeeCombine $condCombineFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param ExtensionAttributesFactory|null $extensionFactory
     * @param AttributeValueFactory|null $customAttributeFactory
     * @param Json|null $serializer
     */

    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        CombineFactory $condProdCombineF,
        ExtraFeeCombine $condCombineFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        ExtensionAttributesFactory $extensionFactory = null,
        AttributeValueFactory $customAttributeFactory = null,
        Json $serializer = null
    ) {
        $this->condProdCombineF   = $condProdCombineF;
        $this->condCombineFactory = $condCombineFactory;

        parent::__construct(
            $context,
            $registry,
            $formFactory,
            $localeDate,
            $resource,
            $resourceCollection,
            $data,
            $extensionFactory,
            $customAttributeFactory,
            $serializer
        );
    }

    /**
     * Init resource model
     */
    protected function _construct()
    {
        $this->_init(RuleResource::class);
    }

    /**
     * Get action instance
     *
     * @return Collection|AbstractModel\Condition\Product\Combine
     */
    public function getActionsInstance()
    {
        return $this->condProdCombineF->create();
    }

    /**
     * @return Combine|Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->condCombineFactory->create();
    }
}
