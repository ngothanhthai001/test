<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2023 Amasty (https://www.amasty.com)
 * @package Free Gift Base for Magento 2
 */

namespace Amasty\Promo\Block\Catalog\Product\View\Type;

use Magento\Bundle\Block\Catalog\Product\View\Type\Bundle as BundleType;
use Magento\Bundle\Model\Product\PriceFactory;
use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product as CatalogProduct;
use Magento\CatalogRule\Model\ResourceModel\Product\CollectionProcessor;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\ArrayUtils;

class Bundle extends BundleType
{
    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        CatalogProduct $catalogProduct,
        PriceFactory $productPrice,
        EncoderInterface $jsonEncoder,
        FormatInterface $localeFormat,
        Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct(
            $context,
            $arrayUtils,
            $catalogProduct,
            $productPrice,
            $jsonEncoder,
            $localeFormat,
            $data
        );
    }

    /**
     * fix fatal when $product = null in \Magento\Tax\Observer\GetPriceConfigurationObserver::execute()
     *
     * @return string
     */
    public function getJsonConfig()
    {
        $this->registry->register('current_product', $this->getProduct(), true);
        $result = parent::getJsonConfig();
        $this->registry->unregister('current_product');

        return $result;
    }
}
