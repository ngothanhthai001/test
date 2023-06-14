<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Test\Unit\Model\Quote;

use Amasty\Promo\Api\Data\CounterInterfaceFactory;
use Amasty\Promo\Helper\Data;
use Amasty\Promo\Api\Data\CounterInterface;
use Amasty\Promo\Model\Product;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class PromoItemCounterTest extends TestCase
{
    /**
     * @var \Amasty\Promo\Model\Quote\PromoItemCounter
     */
    private $subject;

    /**
     * @var \Amasty\Promo\Helper\Data|MockObject
     */
    private $promoHelperMock;

    /**
     * @var CounterInterfaceFactory|MockObject
     */
    private $counterInterfaceFactoryMock;

    protected function setUp(): void
    {
        $this->promoHelperMock = $this->createMock(Data::class);
        $this->counterInterfaceFactoryMock = $this->createMock(CounterInterfaceFactory::class);
        $productStockProviderMock = $this->createMock(Product::class);
        $this->subject = new \Amasty\Promo\Model\Quote\PromoItemCounter(
            $this->promoHelperMock,
            $productStockProviderMock,
            $this->counterInterfaceFactoryMock
        );
    }

    /**
     * @dataProvider getPromoCountsProvider
     */
    public function testGetPromoCounts($expected, $itemsArray, $newItemsReturn)
    {
        $items = $this->prepareQuoteItems($itemsArray);
        $newItemsReturn = $this->prepareQuoteItems($newItemsReturn);
        $quote = $this->createMock(\Magento\Quote\Model\Quote::class);
        $quote->expects($this->any())->method('getAllVisibleItems')->willReturn($items);
        $this->promoHelperMock->method('getNewItems')->willReturn($newItemsReturn);
        $this->promoHelperMock->method('getPromoItemsDataArray')->willReturn(
            ['common_qty' => $expected[CounterInterface::KEY_AVAILABLE]]
        );
        $counter = $this->createMock(CounterInterface::class);
        $this->counterInterfaceFactoryMock->method('create')->willReturnCallback(
            function (array $arguments) use ($counter) {
                $counter->method('getSelectedCount')->willReturn(
                    $arguments['data'][CounterInterface::KEY_SELECTED] ?? 0
                );
                $counter->method('getAvailableCount')->willReturn(
                    $arguments['data'][CounterInterface::KEY_AVAILABLE] ?? 0
                );
                return $counter;
            }
        );
        $result = $this->subject->getPromoCounts($quote);

        $this->assertEquals($expected[CounterInterface::KEY_SELECTED], $result->getSelectedCount());
    }

    /**
     * @return array[]
     */
    public function getPromoCountsProvider(): array
    {
        return [
            [
                [
                    CounterInterface::KEY_AVAILABLE => 0,
                    CounterInterface::KEY_SELECTED => 0
                ],
                [],
                []
            ],

            [
                [
                    CounterInterface::KEY_AVAILABLE => 1,
                    CounterInterface::KEY_SELECTED => 10
                ],
                [
                    [
                        'am_rule_id' => 1,
                        'qty' => 10
                    ],
                    [
                        'am_rule_id' => null,
                        'qty' => 1
                    ],
                ],
                [
                    [
                        'am_rule_id' => 1,
                        'qty' => 10
                    ],
                ],
            ],

            [
                [
                    CounterInterface::KEY_AVAILABLE => 0,
                    CounterInterface::KEY_SELECTED => 0
                ],
                [
                    [
                        'am_rule_id' => null,
                        'qty' => 1
                    ],
                    [
                        'am_rule_id' => null,
                        'qty' => 1
                    ],
                ],
                []
            ]
        ];
    }

    /**
     * @param array $itemsArray
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    private function prepareQuoteItems(array $itemsArray): array
    {
        $items = [];
        foreach ($itemsArray as $itemData) {
            $item = $this->createMock(\Magento\Quote\Model\Quote\Item::class);
            $item->expects($this->any())
                ->method('__call')
                ->withConsecutive(
                    ['getAmpromoRuleId', []],
                )->willReturnOnConsecutiveCalls(
                    $itemData['am_rule_id']
                );
            $item->expects($this->any())
                ->method('getQty')
                ->willReturn($itemData['qty']);
            $items[] = $item;
        }

        return $items;
    }
}
