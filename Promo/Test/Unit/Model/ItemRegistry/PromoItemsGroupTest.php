<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Test\Unit\Model\ItemRegistry;

use Amasty\Promo\Model\ItemRegistry\PromoItemsGroup;
use Amasty\Promo\Test\Unit\Traits;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @see \Amasty\Promo\Model\ItemRegistry\PromoItemsGroup
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class PromoItemsGroupTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var PromoItemsGroup|MockObject
     */
    private $promoItemsGroup;

    /**
     * @var \Amasty\Promo\Model\ItemRegistry\PromoItemData|MockObject
     */
    private $promoItem;

    /**
     * @var \Amasty\Promo\Model\ItemRegistry\PromoItemFactory|MockObject
     */
    private $factory;

    public function setUp(): void
    {
        $this->promoItemsGroup = $this->createPartialMock(
            PromoItemsGroup::class, ['qtyAction']
        );

        $this->factory = $this->createMock(\Amasty\Promo\Model\ItemRegistry\PromoItemDataFactory::class);

        $this->setProperty(
            $this->promoItemsGroup,
            'factory',
            $this->factory,
            PromoItemsGroup::class
        );
    }

    /**
     * @covers PromoItemsGroup::registerItem
     * @dataProvider registerItemDataProvider
     */
    public function testRegisterItem($sku, $qty, $ruleId, $expectedStorageCount)
    {
        $this->initPromoItem();
        $this->setProperty(
            $this->promoItemsGroup,
            'storage',
            [$this->promoItem],
            PromoItemsGroup::class
        );

        $itemData = [
            'sku' => $sku,
            'allowed_qty' => $qty,
            'rule_id' => $ruleId,
            'rule_type' => 0,
            'minimal_price' => null,
            'discount_item' => null,
            'discount_amount' => null
        ];
        $this->factory->expects($this->any())->method('create')
            ->with(['data' => $itemData])
            ->willReturnCallback(
                function ($itemData) {
                    $this->promoItem->setSku($itemData['data']['sku'])->setRuleId($itemData['data']['rule_id']);

                    return $this->promoItem;
                }
            );

        $result = $this->promoItemsGroup->registerItem($sku, $qty, $ruleId);
        $this->assertEquals($this->promoItem, $result);
        $storageCount = count(
            $this->getProperty($this->promoItemsGroup, 'storage', PromoItemsGroup::class)
        );
        $this->assertEquals($expectedStorageCount, $storageCount);
    }

    /**
     * @covers PromoItemsGroup::getItemBySkuAndRuleId
     * @dataProvider getItemBySkuAndRuleIdDataProvider
     */
    public function testGetItemBySkuAndRuleId($sku, $ruleId, $expected)
    {
        $this->initPromoItem();
        $this->setProperty(
            $this->promoItemsGroup,
            'storage',
            [$this->promoItem],
            PromoItemsGroup::class
        );

        $result = $this->promoItemsGroup->getItemBySkuAndRuleId($sku, $ruleId);
        $this->assertEquals($expected, $result);
    }

    /**
     * @covers PromoItemsGroup::assignQtyToItem
     * @dataProvider assignQtyToItemDataProvider
     */
    public function testAssignQtyToItem($ruleType)
    {
        $qty = 1;
        $this->initPromoItem();
        $this->setProperty(
            $this->promoItemsGroup,
            'storage',
            [$this->promoItem],
            PromoItemsGroup::class
        );
        $this->promoItem->setRuleType($ruleType);

        $this->promoItemsGroup->expects($this->once())->method('qtyAction')
            ->with($qty, $this->promoItem, PromoItemsGroup::QTY_ACTION_RESERVE);

        $this->promoItemsGroup->assignQtyToItem($qty, $this->promoItem, PromoItemsGroup::QTY_ACTION_RESERVE);
    }

    /**
     * Data Provider for testGetItemBySkuAndRuleId test
     * @return array
     */
    public function getItemBySkuAndRuleIdDataProvider()
    {
        $this->initPromoItem();

        return [
            ['test_sku2', 1, null],
            ['test_sku', 2, null],
            ['test_sku', 1, $this->promoItem]
        ];
    }

    /**
     * Data Provider for assignQtyToItem test
     * @return array
     */
    public function assignQtyToItemDataProvider()
    {
        return [
            [1],
            [2]
        ];
    }

    /**
     * Data Provider for registerItem test
     * @return array
     */
    public function registerItemDataProvider()
    {
        return [
            ['test_sku', 1, 1, 1],
            ['test_sku2', 1, 2, 2]
        ];
    }

    /**
     * Init promo item mock for tests
     */
    private function initPromoItem()
    {
        $this->promoItem = $this->createPartialMock(
            \Amasty\Promo\Model\ItemRegistry\PromoItemData::class,
            []
        );
        $this->promoItem->setSku('test_sku')->setRuleId(1);
    }
}
