<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Advanced Conditions for Magento 2
 */

namespace Amasty\Conditions\Controller\Adminhtml\ProductCustomOptions;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Layout\Builder;

/**
 * Custom Options Grid Controller Action
 * @since 1.4.0
 */
class ChooserGrid extends \Magento\Backend\App\Action
{
    /**
     * Path to this action
     */
    public const URL_PATH = "amasty_conditions/productCustomOptions/chooserGrid";

    /**
     * Authorization level of a basic admin session
     */
    public const ADMIN_RESOURCE = 'Magento_SalesRule::quote';

    /**
     * @var Builder
     */
    private $builder;
    
    public function __construct(
        Context $context,
        Builder $builder
    ) {
        $this->builder = $builder;
        parent::__construct($context);
    }

    /**
     * Grid ajax action in chooser mode
     *
     * @return void
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $this->builder->build();
        
        return $resultPage;
    }
}
