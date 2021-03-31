<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

namespace IctMasterPk\PricemeterProductsSync\Observer\Backend\Catalog;

use IctMasterPk\PricemeterProductsSync\Helper\Data;
use IctMasterPk\PricemeterProductsSync\Helper\Product;

class ProductSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    protected $logger;

    /**
     * @var Product
     */
    protected $productHelper;

    protected $_messageManager;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        Product $productHelper
    ) {
        $this->logger = $logger;
        $this->productHelper = $productHelper;
        $this->_messageManager = $messageManager;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $_product = $observer->getProduct();

        // If sync status got changed, then it will only occur programmatically and only that field got changed,
        // so instead of resending api call just ignore event for that change,
        // or else it will resend update api call
        if ($_product->dataHasChangedFor(Data::PM_SYNC_STATUS)) {
            return;
        }

        $response = $this->productHelper->insertOrUpdatePmProduct($_product);

        if (!empty($response['message'])) {
            if ($response['status'] == true) {
                $this->_messageManager->addSuccessMessage(__($response['message']));
            } else {
                $this->_messageManager->addErrorMessage(__($response['message']));
            }
        }
    }
}
