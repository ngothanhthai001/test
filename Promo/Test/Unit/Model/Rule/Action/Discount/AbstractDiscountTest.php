<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Test\Unit\Model\Rule\Action\Discount;

use Amasty\Promo\Model\Rule\Action\Discount\AbstractDiscount;
use Amasty\Promo\Model\Rule\ItemsStorage;
use Amasty\Promo\Test\Unit\Traits;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class AbstractDiscountTest
 *
 * @see AbstractDiscount
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * phpcs:ignoreFile
 */
class AbstractDiscountTest extends \PHPUnit\Framework\TestCase
{
    use Traits\ObjectManagerTrait;
    use Traits\ReflectionTrait;

    /**
     * @var AbstractDiscount
     */
    private $model;

    /**
     * @covers AbstractDiscount::getPromoQtyByStep
     *
     * @param int $discountAmount
     * @param int $discountStep
     * @param int $discountQty
     * @param int $itemQty
     * @param float|int $expectedResult
     *
     * @dataProvider getPromoQtyByStepDataProvider
     *
     * @throws \ReflectionException
     */
    public function testGetPromoQtyByStep($discountAmount, $discountStep, $discountQty, $itemQty, $expectedResult = 0.0)
    {
        $this->model = $this->createMock(AbstractDiscount::class);

        $itemMock = $this->initItem($itemQty);
        $itemsStorageMock = $this->createMock(ItemsStorage::class);
        $itemsStorageMock->expects($this->once())
            ->method('getItems')
            ->willReturn([$itemMock]);
        $itemsStorageMock->expects($this->once())
            ->method('getValidItemsForRule')
            ->willReturn([$itemMock]);

        $this->setProperty(
            $this->model,
            'itemsStorage',
            $itemsStorageMock,
            AbstractDiscount::class
        );

        $ruleMock = $this->initRule($discountAmount, $discountStep, $discountQty);

        $result = $this->invokeMethod($this->model, 'getPromoQtyByStep', [$ruleMock, $itemMock]);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Init rule for test
     * @return \Magento\SalesRule\Model\Rule|MockObject
     */
    private function initRule($discountAmount, $discountStep, $discountQty)
    {
        $rule = $this->getMockBuilder(\Magento\SalesRule\Model\Rule::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDiscountAmount', 'getDiscountStep', 'getName', 'getDiscountQty', 'getActions'])
            ->getMock();
        $actions = $this->createMock(\Magento\Rule\Model\Action\Collection::class);

        $rule->expects($this->any())->method('getDiscountAmount')->willReturn($discountAmount);
        $rule->expects($this->any())->method('getDiscountStep')->willReturn($discountStep);
        $rule->expects($this->any())->method('getName')->willReturn('rule_name');
        $rule->expects($this->any())->method('getDiscountQty')->willReturn($discountQty);
        $rule->expects($this->any())->method('getActions')->willReturn($actions);

        return $rule;
    }

    /**
     * @param $itemQty
     * @return AbstractItem|\PHPUnit\Framework\MockObject\MockObject
     */
    private function initItem($itemQty)
    {
        $itemMock = $this->createMock(AbstractItem::class);
        $addressMock = $this->createMock(Address::class);

        $itemMock->expects($this->once())
            ->method('getQty')
            ->willReturn($itemQty);
        $itemMock->expects($this->once())
            ->method('getAddress')
            ->willReturn($addressMock);

        return $itemMock;
    }

    /**
     * Data provider for getPromoQtyByStep test
     * @return array
     */
    public function getPromoQtyByStepDataProvider()
    {
        return [
            [1, 1, 1, 1, 1],
            [1, 5, 10, 1],
            [10, 50, 100, 1],
            [0, 0, 0, 1, 1],
            [0, 0, 0, 5, 5],
            [10, 50, 55, 5],
            [1, 1, 1, 5, 1],
        ];
    }
}
