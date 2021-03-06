<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace IctMasterPk\PricemeterProductsSync\Observer\Backend\Catalog;

use IctMasterPk\PricemeterProductsSync\Helper\Product;

class ProductDeleteAfterDone implements \Magento\Framework\Event\ObserverInterface
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

        try {
            $response = $this->productHelper->deletePmProduct($_product->getId());
        } catch (\Exception $e) {
            $response = [
                'status' => false,
                'message' => ''
            ];
        }

        if (!empty($response['message'])) {
            if ($response['status'] == true) {
                $this->_messageManager->addSuccessMessage(__($response['message']));
            } else {
                $this->_messageManager->addErrorMessage(__($response['message']));
            }
        }
    }
}

