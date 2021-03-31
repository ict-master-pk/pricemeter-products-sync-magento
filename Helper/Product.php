<?php

namespace IctMasterPk\PricemeterProductsSync\Helper;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Product extends AbstractHelper
{
    /**
     * @var Data
     */
    private $helperData;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    private $_categoryFactory;

    /**
     * @var CollectionFactory
     */
    private $categoryCollection;

    /**
     * @var \Magento\Review\Model\ReviewFactory
     */
    private $_reviewFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /**
     * @var \Magento\Review\Model\RatingFactory
     */
    protected $_ratingFactory;

    protected $_storeManager;

    protected $_frontUrlModel;

    protected $_helperFactory;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $appEmulation;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_magProductHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Action
     */
    protected $_productAction;

    protected $_productRepository;

    public function __construct(
        Context $context,
        Data $helperData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $reviewFactory,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        CollectionFactory $categoryCollection,
        \Magento\Framework\Url $frontUrlModel,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Catalog\Helper\Product $magProductHelper,
        \Magento\Catalog\Model\ResourceModel\Product\Action $productAction,
        ProductRepository $productRepository,
        \Magento\Catalog\Helper\ImageFactory $helperFactory
    ) {
        parent::__construct($context);

        $this->helperData = $helperData;
        $this->_storeManager = $storeManager;
        $this->productFactory = $productFactory;
        $this->_ratingFactory = $ratingFactory;
        $this->_reviewFactory = $reviewFactory;
        $this->_categoryFactory = $categoryFactory;
        $this->categoryCollection = $categoryCollection;
        $this->_frontUrlModel = $frontUrlModel;
        $this->_imageHelper = $imageHelper;
        $this->_magProductHelper = $magProductHelper;
        $this->_productAction = $productAction;
        $this->_productRepository = $productRepository;
        $this->_helperFactory = $helperFactory;
    }

    public function getRatingCollection($productId, $returnAvgRating = true)
    {
        $reviewsCollection = $this->_reviewFactory->create()
            ->addFieldToSelect('*')
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->addEntityFilter('product', $productId)
            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
            ->setDateOrder()
            ->addRateVotes();

        $avgRating = [];
        foreach ($reviewsCollection as $review) {
            $countRatings = count($review->getRatingVotes());
            $allRatings = 0;
            foreach ($review->getRatingVotes() as $vote) {
                $allRatings = $allRatings + $vote->getPercent();
            }
            $avgRating[] = $allRatings / $countRatings;
        }

        if ($returnAvgRating) {
            $rating = empty($avgRating) ? 0 : array_sum($avgRating) / count($avgRating);
            if ($rating > 0) {
                $rating = (5 * $rating) / 100;
            }

            return round($rating, 1);
        }

        return $reviewsCollection->getData();
    }

    private function getFilteredCategoryCollection($categoryIds)
    {
        $collection = $this->categoryCollection->create();
        $filtered_colection = $collection
            ->addFieldToSelect('*')
            ->addFieldToFilter(
                'entity_id',
                ['in' => $categoryIds]
            )
            ->setOrder('level', 'ASC')
            ->load();
        return $filtered_colection;
    }

    /**
     * Convert magento product object to Price Meter API array
     *
     * @param $product
     * @return array
     */
    public function preparePMProductArray(\Magento\Catalog\Model\Product $product)
    {
        $categories = [];
        $categoryIds = $product->getCategoryIds();
        $filtered_collection = $this->getFilteredCategoryCollection($categoryIds);
        foreach ($filtered_collection as $categoriesData) {
            $categories[] = $categoriesData->getData('name');
        }

        $storeId = array_first($product->getStoreIds());
        $prodUrl = $product->setStoreId($storeId)->getUrlInStore();
        $prodImgUrl = $this->_magProductHelper->getImageUrl($product);

        return [
            'id' => $product->getId(),
            'sku' => $product->getSku(),
            'upc' => '',
            'title' => $product->getName(),
            'brand' => $product->getStore()->getName(),
            'category' => implode(' > ', $categories),
            'price' => round($product->getPrice(), 2),
            'discounted_price' => round(is_null($product->getSpecialPrice()) ? "" : $product->getSpecialPrice(), 2),
            'image_url' => $prodImgUrl,
            'url' => $prodUrl,
            'description' => trim($product->getDescription()),
            'availability' => $product->isInStock(),
            'rating' => $this->getRatingCollection($product->getId()),
            'keywords' => $product->getData(Data::PM_KEYWORDS)
        ];
    }

    public static function beautifyResponseMessage($response)
    {
        // Formatting response array message
        if (isset($response['message']) && is_array($response['message'])) {
            $response['message'] = implode(' ', array_map(function ($value) {
                return is_array($value) ? reset($value) : $value;
            }, $response['message']));
        }

        $response['message'] = (is_int(strpos($response['message'], 'Price Meter')) ? '' : "Price Meter - ") . $response['message'];

        return $response;
    }

    /**
     * @return \Pricemeter\Model\Product
     * @throws \Exception
     */
    public function getPmApiObject()
    {
        $apiToken = $this->helperData->getApiToken();
        if (!empty($apiToken)) {
            return new \Pricemeter\Model\Product($apiToken);
        } else {
            throw new \Exception("Price Meter API token not set");
        }
    }

    /**
     * Make Price Meter create or update API call by detecting sync status
     *
     * @param $product
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function insertOrUpdatePmProduct(\Magento\Catalog\Model\Product $product)
    {
        $sync_status = strtolower($product->getAttributeText(Data::PM_SYNC_STATUS)) == 'yes';

        // Check product status, if not active then send delete request
        if ($product->getStatus() != Status::STATUS_ENABLED) {
            if ($sync_status == true) {
                $response = $this->deletePmProduct($product->getId());

                // Remove sync status to avoid deletion again and again
                $product->setData(Data::PM_SYNC_STATUS, false);
                $this->_productRepository->save($product);

                return $response;
            } else {
                // No need to send API request as data is not supposed to be on Price Meter
                return [
                    'status' => true,
                    'message' => ''
                ];
            }
        }

        $productArr = $this->preparePMProductArray($product);

        $pmObj = $this->getPmApiObject();
        $pmObj->fill($productArr);

        try {
            if ($sync_status) {
                $response = self::beautifyResponseMessage($pmObj->update());
            } else {
                $response = $pmObj->insert();

                // Update sync status
                $product->setData(Data::PM_SYNC_STATUS, true);
                $this->_productRepository->save($product);

                if (isset($response['message']) && is_string($response['message']) && trim($response['message']) == 'Product already exists with given id.') {
                    // Resend update request
                    $response = $this->insertOrUpdatePmProduct($product);
                }

                $response = self::beautifyResponseMessage($response);
            }

            return $response;

        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * @param $product_id
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function deletePmProduct($product_id)
    {
        try {
            $pmObj = $this->getPmApiObject();
            $response = $pmObj->fill([
                'id' => $product_id
            ])->delete();

            return self::beautifyResponseMessage($response);

        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
