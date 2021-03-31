<?php
namespace IctMasterPk\PricemeterProductsSync\Controller\Adminhtml\Catalog;

use Magento\Framework\App\Filesystem\DirectoryList;

class Export extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    private $_fileFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $_directory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->_fileFactory = $fileFactory;
        $this->_directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        parent::__construct($context);
    }

    public function execute()
    {
        $name = 'pm_products_sync_' . date('m_d_Y_H_i_s');
        $filepath = 'export/custom' . $name . '.csv';
        $this->_directory->create('export');
        /* Open file */
        $stream = $this->_directory->openFile($filepath, 'w+');
        $stream->lock();
        /* Write Header */
        $stream->writeCsv($this->getColumnHeader());

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productHelper = $objectManager->create('IctMasterPk\PricemeterProductsSync\Helper\Product');
        $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');

        $collection = $productCollection
            ->addAttributeToSelect('*')
            ->addFieldTofilter('type_id', 'simple')
            ->addFieldToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
            ->load();

        foreach ($collection as $product) {
            if (!empty($product->getId())) {
                $itemData = $productHelper->preparePMProductArray($product);
                $stream->writeCsv($itemData);
            }
        }

        $content = [];
        $content['type'] = 'filename';
        $content['value'] = $filepath;
        $content['rm'] = '1';

        return $this->_fileFactory->create($name . '.csv', $content, DirectoryList::VAR_DIR);
    }

    /* Header Columns */
    public function getColumnHeader()
    {
        return [
            'id',
            'sku',
            'upc',
            'title',
            'brand',
            'category',
            'price',
            'discounted_price',
            'image_url',
            'url',
            'description',
            'availability',
            'rating',
            'keywords'
        ];
    }
}
