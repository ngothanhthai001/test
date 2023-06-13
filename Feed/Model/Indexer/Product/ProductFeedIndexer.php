<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */


namespace Amasty\Feed\Model\Indexer\Product;

use Amasty\Feed\Exceptions\LockProcessException;
use Amasty\Feed\Model\Indexer\AbstractIndexer;
use Amasty\Feed\Model\Indexer\LockManager;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class ProductFeedIndexer extends AbstractIndexer
{
    /**
     * @var LockManager
     */
    private $lockManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Override constructor. Indexer is changed
     *
     * @param IndexBuilder $productIndexBuilder
     * @param ManagerInterface $eventManager
     * @param LockManager $lockManager
     */
    //phpcs:ignore
    public function __construct(
        IndexBuilder $productIndexBuilder,
        ManagerInterface $eventManager,
        LockManager $lockManager,
        LoggerInterface $logger = null // TODO move to not optional
    ) {
        parent::__construct($productIndexBuilder, $eventManager);
        $this->lockManager = $lockManager;
        $this->logger = $logger ?? ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecuteList($productIds)
    {
        try {
            $this->lockManager->lockProcess();
            $this->indexBuilder->reindexByProductIds(array_unique($productIds));
            $this->getCacheContext()->registerEntities(\Magento\Catalog\Model\Product::CACHE_TAG, $productIds);
            $this->lockManager->unlockProcess();
        } catch (LockProcessException $e) {
            $this->logger->debug($e->getMessage());
        } catch (\Exception $e) {
            $this->lockManager->unlockProcess();
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecuteRow($productId)
    {
        try {
            $this->lockManager->lockProcess();
            $this->indexBuilder->reindexByProductId($productId);
            $this->lockManager->unlockProcess();
        } catch (LockProcessException $e) {
            $this->logger->debug($e->getMessage());
        } catch (\Exception $e) {
            $this->lockManager->unlockProcess();
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }

    public function executeFull()
    {
        try {
            $this->lockManager->lockProcess();
            $this->indexBuilder->reindexFull();
            $this->lockManager->unlockProcess();
        } catch (LockProcessException $e) {
            $this->logger->debug($e->getMessage());
            throw new LocalizedException(__($e->getMessage()), $e);
        } catch (\Exception $e) {
            $this->lockManager->unlockProcess();
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }
}
