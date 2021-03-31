# Mage2 Module IctMasterPk PricemeterProductsSync

    ``ictmasterpk/module-pricemeterproductssync``

- [Main Functionalities](#markdown-header-main-functionalities)
- [Installation](#markdown-header-installation)
- [Configuration](#markdown-header-configuration)
- [Specifications](#markdown-header-specifications)
- [Attributes](#markdown-header-attributes)


## Main Functionalities
Price Meter Products Sync is the official Price Meter module for Magento users to allow them easily sync their products on Price Meter without much effort.

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

- Unzip the zip file in `app/code/IctMasterPk`
- Enable the module by running `php bin/magento module:enable IctMasterPk_PricemeterProductsSync`
- Apply database updates by running `php bin/magento setup:upgrade`\*
- Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

- Make the module available in a composer repository for example:
    - private repository `repo.magento.com`
    - public repository `packagist.org`
    - public github repository as vcs
- Add the composer repository to the configuration by running `composer config repositories.repo.magento.com composer https://repo.magento.com/`
- Install the module composer by running `composer require ictmasterpk/module-pricemeterproductssync`
- enable the module by running `php bin/magento module:enable IctMasterPk_PricemeterProductsSync`
- apply database updates by running `php bin/magento setup:upgrade`\*
- Flush the cache by running `php bin/magento cache:flush`


## Configuration

- Price Meter API Token (general/settings/pm_api_token)


## Specifications

- Configuration Type
    - system

- Observer
    - catalog_product_save_after > IctMasterPk\PricemeterProductsSync\Observer\Backend\Catalog\ProductSaveAfter

- Observer
    - catalog_product_delete_after_done > IctMasterPk\PricemeterProductsSync\Observer\Catalog\ProductDeleteAfterDone


## Attributes

- Product - Price Meter Keywords (pm_keywords)

- Product - Price Meter Sync Status (pm_sync_status)
