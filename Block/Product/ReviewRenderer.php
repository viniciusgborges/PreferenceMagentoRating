<?php

namespace Vb\PluginMagentoRating\Block\Product;

use Magento\Catalog\Block\Product\ReviewRendererInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\ObjectManager;
use Magento\Review\Model\ReviewSummaryFactory;
use Magento\Review\Observer\PredispatchReviewObserver;
use Magento\Framework\View\Element\Template;

class ReviewRenderer extends \Magento\Review\Block\Product\ReviewRenderer implements ReviewRendererInterface
{
    protected $_availableTemplates = [
        self::FULL_VIEW => 'Magento_Review::helper/summary.phtml',
        self::SHORT_VIEW => 'Magento_Review::helper/summary_short.phtml',
    ];
    
    protected $_reviewFactory;

    private $reviewSummaryFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        array $data = [],
        ReviewSummaryFactory $reviewSummaryFactory = null
    ) {
        $this->_reviewFactory = $reviewFactory;
        $this->reviewSummaryFactory = $reviewSummaryFactory ??
            ObjectManager::getInstance()->get(ReviewSummaryFactory::class);
        Template::__construct($context, $data);
    }

    public function isReviewEnabled(): string
    {
        return $this->_scopeConfig->getValue(
            PredispatchReviewObserver::XML_PATH_REVIEW_ACTIVE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getReviewsSummaryHtml(
        \Magento\Catalog\Model\Product $product,
        $templateType = self::DEFAULT_VIEW,
        $displayIfNoReviews = false
    ) {
        if ($product->getRatingSummary() === null) {
            $this->reviewSummaryFactory->create()->appendSummaryDataToObject(
                $product,
                $this->_storeManager->getStore()->getId()
            );
        }

        if (null === $product->getRatingSummary() && !$displayIfNoReviews) {
            return '';
        }
        // pick template among available
        if (empty($this->_availableTemplates[$templateType])) {
            $templateType = self::DEFAULT_VIEW;
        }
        $this->setTemplate($this->_availableTemplates[$templateType]);

        $this->setDisplayIfEmpty($displayIfNoReviews);

        $this->setProduct($product);

        return $this->toHtml();
    }

    public function getRatingSummary()
    {
        $this->_reviewFactory->create()->getEntitySummary($this->getProduct(), $this->_storeManager->getStore()->getId());
        return $this->getProduct()->getRatingSummary()->getRatingSummary();
    }

    public function getReviewsCount()
    {
        $this->_reviewFactory->create()->getEntitySummary($this->getProduct(), $this->_storeManager->getStore()->getId());
        return $this->getProduct()->getRatingSummary()->getReviewsCount();
    }

    public function getReviewsUrl($useDirectLink = false)
    {
        $product = $this->getProduct();
        if ($useDirectLink) {
            return $this->getUrl(
                'review/product/list',
                ['id' => $product->getId(), 'category' => $product->getCategoryId()]
            );
        }
        return $product->getUrlModel()->getUrl($product, ['_ignore_category' => true]);
    }
}
