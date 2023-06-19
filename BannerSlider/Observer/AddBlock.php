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
 * @package     Mageplaza_BannerSlider
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\BannerSlider\Observer;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Layout;
use Mageplaza\BannerSlider\Block\Slider;
use Mageplaza\BannerSlider\Helper\Data;
use Mageplaza\BannerSlider\Model\Config\Source\Location;
use Magento\Framework\Registry;

/**
 * Class AddBlock
 * @package Mageplaza\AutoRelated\Observer
 */
class AddBlock implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected \Magento\Framework\UrlInterface $urlBuilder;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * AddBlock constructor.
     *
     * @param RequestInterface $request
     * @param Data $helperData
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param Registry $coreRegistry
     */
    public function __construct(
        RequestInterface $request,
        Data $helperData,
        \Magento\Framework\UrlInterface $urlBuilder,
        Registry $coreRegistry
    ) {
        $this->request = $request;
        $this->helperData = $helperData;
        $this->urlBuilder = $urlBuilder;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * @param Observer $observer
     *
     * @return $this
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        if (!$this->helperData->isEnabled()) {
            return $this;
        }

        $type = array_search($observer->getEvent()->getElementName(), [
            'header' => 'header',
            'content' => 'content',
            'page-top' => 'page.wrapper',
            'footer-container' => 'footer-container',
            'sidebar' => 'catalog.leftnav'
        ], true);

        if ($type !== false) {
            /** @var Layout $layout */
            $layout = $observer->getEvent()->getLayout();
            $fullActionName = $this->request->getFullActionName();
            $output = $observer->getTransport()->getOutput();
            $currentUrl     = $this->urlBuilder->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]) ;
            foreach ($this->helperData->getActiveSliders() as $slider) {
                $locations = array_filter(explode(',', $slider->getLocation()));
                $urlLocation = $slider->getUrlLocation();
                $displayOn = $slider->getDisplayOn() == 0 ? 'desktop' : 'mobile';
                if($urlLocation != null) {
                    if (strpos($currentUrl, $urlLocation) !== false) {
                        foreach ($locations as $value) {
                            if ($value === Location::USING_SNIPPET_CODE) {
                                continue;
                            }
                            [$pageType, $location] = explode('.', $value);
                            if (strpos($location, $type) !== false) {
                                $content = $layout->createBlock(Slider::class)
                                    ->setSlider($slider)
                                    ->toHtml();
                                if (strpos($location, 'top') !== false) {
                                    if ($type === 'sidebar') {
                                        $output = "<div class=\"slide-{$displayOn} mp-banner-sidebar\" id=\"mageplaza-bannerslider-block-before-{$type}-{$slider->getId()}\">
                                        $content</div>" . $output;
                                    } else {
                                        $output = "<div class=\"slide-{$displayOn} mageplaza-bannerslider-block-before-{$type} mp-banner-sidebar\" id=\"slide-{$displayOn} mageplaza-bannerslider-block-before-{$type}-{$slider->getId()}\">
                                        $content</div>" . $output;
                                    }
                                } else {
                                    if ($type === 'sidebar') {
                                        $output .= "<div class=\"slide-{$displayOn} mp-banner-sidebar\" id=\"mageplaza-bannerslider-block-after-{$type}-{$slider->getId()}\">
                                        $content</div>";
                                    } else {
                                        $output .= "<div id=\"slide-{$displayOn} mageplaza-bannerslider-block-after-{$type}-{$slider->getId()}\">
                                        $content</div>";
                                    }
                                }
                            }
                        }
                    }
                } else {
                    foreach ($locations as $value) {
                        if ($value === Location::USING_SNIPPET_CODE) {
                            continue;
                        }
                        [$pageType, $location] = explode('.', $value);
                        if (($fullActionName === $pageType || $pageType === 'allpage') &&
                            strpos($location, $type) !== false
                        ) {
                            $content = $layout->createBlock(Slider::class)
                                ->setSlider($slider)
                                ->toHtml();
                            if (strpos($location, 'top') !== false) {
                                if ($type === 'sidebar') {
                                    $output = "<div class=\"slide-{$displayOn} mp-banner-sidebar\" id=\"mageplaza-bannerslider-block-before-{$type}-{$slider->getId()}\">
                                        $content</div>" . $output;
                                } elseif ($value == 'cms_index_index.page-top') {
                                    $blocks = $layout->createBlock('Magento\Cms\Block\Block')->setBlockId('bannerslider_right')->toHtml();
                                    if($displayOn === "desktop") {
                                        $output = "<div class=\"slide-{$displayOn} px-0 bg-gray section-banner\">
                                            <div class='container'>
                                                <div id=\"slide-{$displayOn} mageplaza-bannerslider-block-before-{$type}-{$slider->getId()}\" class=\"row px-0 banner-slider-top slide-{$displayOn} mageplaza-bannerslider-block-{$slider->getId()}\">
                                                    <div class=\"col-lg-9 col-9\">$content</div>
                                                    <div class=\"bannerslider_right col-lg-3 col-3 pl-lg-0\">$blocks</div>
                                                </div>
                                            </div>
                                        </div>" . $output;
                                    } else {
                                        $output = "<div id=\"slide-{$displayOn} mageplaza-bannerslider-block-before-{$type}-{$slider->getId()}\" class=\"banner-slider-top slide-{$displayOn} mageplaza-bannerslider-block-{$slider->getId()}\">$content</div>
                                        <div class=\"bannerslider_right col-12 slide-mobile d-lg-none\">$blocks</div>
                                                ". $output;
                                    }
                                } elseif ($value == 'catalog_category_view.page-top') {
                                    $category = $this->coreRegistry->registry('current_category');
                                    $cateName = $category->getName();
                                    $slideName = $slider->getName();
                                    $firstName = strtok($slideName, '_');
                                    if ($firstName == $cateName) {
                                        $output = "<div id=\"slide-{$displayOn} mageplaza-bannerslider-block-before-{$type}-{$slider->getId()}\" class=\"banner-slider-top slide-{$displayOn} mageplaza-bannerslider-block-before-{$type} mageplaza-bannerslider-block-{$slider->getId()} \">$content</div>" . $output;
                                    }
                                } elseif ($value == 'blog_category_index.content-top') {
                                    $output = '<div id="blog-hi">' . $content . '</div>' . $output;
                                } else {
                                    $output = "<div id=\"slide-{$displayOn} mageplaza-bannerslider-block-before-{$type}-{$slider->getId()}\" class=\"banner-slider-top slide-{$displayOn} mageplaza-bannerslider-block-{$slider->getId()} \">$content</div>" . $output;
                                }
                            } else {
                                if ($type === 'sidebar') {
                                    $output .= "<div class=\"slide-{$displayOn} mp-banner-sidebar\" id=\"mageplaza-bannerslider-block-after-{$type}-{$slider->getId()}\">
                                        $content</div>";
                                } else {
                                    $output .= "<div class=\"slide-{$displayOn} mp-banner-sidebar\" id=\"slide-{$displayOn} mageplaza-bannerslider-block-after-{$type}-{$slider->getId()}\">
                                        $content</div>";
                                }
                            }
                        }
                    }
                }
            }

            $observer->getTransport()->setOutput($output);
        }

        return $this;
    }
}
