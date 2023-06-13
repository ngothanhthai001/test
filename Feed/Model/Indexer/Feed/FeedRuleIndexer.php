<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Product Feed for Magento 2
 */


namespace Amasty\Feed\Model\Indexer\Feed;

use Amasty\Feed\Exceptions\LockProcessException;
use Amasty\Feed\Model\Indexer\AbstractIndexer;
use Amasty\Feed\Model\Indexer\LockManager;
use Magento\Framework\App\Cache\Type\Block;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class FeedRuleIndexer extends AbstractIndexer
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
     * @param IndexBuilder $indexBuilder
     * @param ManagerInterface $eventManager
     * @param LockManager $lockManager
     */
    public function __construct(
        IndexBuilder $indexBuilder,
        ManagerInterface $eventManager,
        LockManager $lockManager,
        LoggerInterface $logger = null // TODO move to not optional
    ) {
        parent::__construct($indexBuilder, $eventManager);
        $this->indexBuilder = $indexBuilder;
        $this->lockManager = $lockManager;
        $this->logger = $logger ?? ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function doExecuteList($ids)
    {
        try {
            $this->lockManager->lockProcess();
            $this->indexBuilder->reindexByFeedIds(array_unique($ids));
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
    protected function doExecuteRow($id)
    {
        try {
            $this->lockManager->lockProcess();
            $this->indexBuilder->reindexByFeedId($id);
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
    public function getIdentities()
    {
        return [
            Block::CACHE_TAG
        ];
    }

    /**
     * {@inheritdoc}
     */
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
