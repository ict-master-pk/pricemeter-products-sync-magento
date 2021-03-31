<?php
namespace IctMasterPk\PricemeterProductsSync\Model\Export;

class PriceMeterProducts extends \Magento\CatalogImportExport\Model\Export\Product
{
    protected $_fields = [
        'id', 'sku', 'upc', 'title', 'brand', 'category', 'price', 'discounted_price', 'image_url', 'url',
        'description', 'availability', 'rating', 'keywords'
    ];

    /**
     * Permanent entity columns.
     *
     * @var string[]
     */
    protected $_permanentAttributes = [
        'id', 'sku', 'upc', 'title', 'brand', 'category', 'price', 'discounted_price', 'image_url', 'url',
        'description', 'availability', 'rating', 'keywords'
    ];

    /**
     * {@inheritdoc}
     */
    public function _getHeaderColumns()
    {
        return $this->_fields;
    }

    /**
     * {@inheritdoc}
     */
    protected function _customFieldsMapping($rowData)
    {
        echo '<pre>';
        print_r($rowData);
        echo '</pre>';
        die();
//        $row = $this->_productHelper->preparePMProductArray($item);
//        foreach ($this->_fieldsMap as $systemFieldName => $fileFieldName) {
//            if (isset($rowData[$systemFieldName])) {
//                $rowData[$fileFieldName] = $rowData[$systemFieldName];
//                unset($rowData[$systemFieldName]);
//            }
//        }
//        return $rowData;
    }

    /**
     * {@inheritdoc}
     */
    protected function _customHeadersMapping($rowData)
    {
        return $this->_fields;
    }
}
