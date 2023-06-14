<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Test\Unit\Model\ItemRegistry;

use Amasty\Promo\Api\Data\GiftRuleInterface;
use Amasty\Promo\Model\ItemRegistry\PromoItemData;
use Amasty\Promo\Model\ItemRegistry\PromoItemRemover;
use Amasty\Promo\Model\ResourceModel\Rule;
use Amasty\Promo\Model\RuleData;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @see PromoItemRemover
 */
class PromoItemRemoverTest extends TestCase
{
    private const RULE_ID = 1;

    /**
     * @var Rule|MockObject
     */
    private $ruleMock;

    /**
     * @var RuleInterface|MockObject
     */
    private $ruleEntityMock;

    /**
     * @var PromoItemRemover
     */
    private $subject;

    public function setUp(): void
    {
        $this->ruleMock = $this->createMock(Rule::class);
        $this->ruleEntityMock = $this->createMock(RuleInterface::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $ruleDataMock = $this->createConfiguredMock(
            RuleData::class,
            ['getRuleByLinkId' => $this->ruleEntityMock]
        );

        $this->ruleRepository = $this->createConfiguredMock(
            RuleRepositoryInterface::class,
            ['getById' => $this->ruleEntityMock]
        );
        $this->subject = new PromoItemRemover($this->ruleMock, $loggerMock, $ruleDataMock);
    }

    /**
     * @covers PromoItemRemover::execute
     *
     * @param string[] $initialItemsSkus
     * @param array[] $ruleSkus
     * @param string[] $resultItemsSkus
     * @return void
     *
     * @dataProvider executeDataProvider
     */
    public function testExecute(array $initialItemsSkus, array $ruleSkus, array $resultItemsSkus): void
    {
        $this->execute($initialItemsSkus, $ruleSkus, $resultItemsSkus, GiftRuleInterface::EACHN);
    }

    private function execute(array $initialItemsSkus, array $ruleSkus, array $resultItemsSkus, string $ruleAction): void
    {
        $initialItems = [];
        $resultItems = [];
        foreach ($initialItemsSkus as $sku) {
            $initialItems[] = $this->initItem($sku);
        }
        foreach ($resultItemsSkus as $sku) {
            $resultItems[] = $this->initItem($sku);
        }

        $this->ruleEntityMock->method('getSimpleAction')->willReturn($ruleAction);
        $this->ruleMock->expects($this->once())->method('isApplicable')->willReturn($ruleSkus);

        $this->assertEquals($resultItems, $this->subject->execute($initialItems));
    }

    /**
     * @covers PromoItemRemover::execute
     *
     * @param string[] $initialItemsSkus
     * @param array[] $ruleSkus
     * @param string[] $resultItemsSkus
     * @return void
     *
     * @dataProvider executeSameProductActionDataProvider
     */
    public function testSameProductActionExecute(array $initialItemsSkus, array $ruleSkus, array $resultItemsSkus): void
    {
        $this->execute($initialItemsSkus, $ruleSkus, $resultItemsSkus, GiftRuleInterface::SAME_PRODUCT);
    }

    /**
     * @return array[]
     */
    public function executeDataProvider(): array
    {
        return [
            [[], [], []],
            [['sku1', 'sku2', 'sku3'], [0 => ['sku' => 'sku1,sku2,sku3']], ['sku1', 'sku2', 'sku3']],
            [['sku1', 'sku2', 'sku3'], [0 => ['sku' => 'sku1,sku2']], ['sku1', 'sku2']]
        ];
    }

    /**
     * @return array[]
     */
    public function executeSameProductActionDataProvider(): array
    {
        return [
            [[], [], []],
            [['sku1', 'sku2', 'sku3'], [0 => ['sku' => 'sku1,sku2,sku3']], ['sku1', 'sku2', 'sku3']],
            [['sku1', 'sku2', 'sku3'], [0 => ['sku' => 'sku1,sku2']], ['sku1', 'sku2', 'sku3']]
        ];
    }

    private function initItem(string $sku): PromoItemData
    {
        return $this->createConfiguredMock(PromoItemData::class, ['getSku' => $sku, 'getRuleId' => self::RULE_ID]);
    }
}
