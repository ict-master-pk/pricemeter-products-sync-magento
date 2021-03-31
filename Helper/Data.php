<?php

namespace IctMasterPk\PricemeterProductsSync\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const PM_SECTION = 'pm_general/';
    const API_TOKEN = 'pm_api_token';

    // tables
    const PM_SYNC_TABLE = 'pm_store_sync';

    const PM_KEYWORDS = 'pm_keywords';
    const PM_SYNC_STATUS = 'pm_sync_status';

    public function getConfigValue($field, $storeCode = null)
    {
        return $this->scopeConfig->getValue($field, ScopeInterface::SCOPE_STORE, $storeCode);
    }

    public function getGeneralConfig($fieldId, $storeCode = null)
    {
        return $this->getConfigValue(self::PM_SECTION . 'pm_settings/' . $fieldId, $storeCode);
    }

    public function getApiToken()
    {
        return $this->getGeneralConfig(self::API_TOKEN);
    }
}
