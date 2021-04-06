# Price Meter Products Sync

    ictmasterpk/module-pricemeterproductssync

## Intro
Price Meter Products Sync is the official Price Meter extension for Magento users to allow them easily sync their products on Price Meter without much effort.

## Installation
\* = in production please use the `--keep-generated` option

### Type 1: Zip file

- Unzip the zip file in `app/code/IctMasterPk`
- Enable the module by running `php bin/magento module:enable IctMasterPk_PricemeterProductsSync`
- Apply database updates by running `php bin/magento setup:upgrade`\*
- Flush the cache by running `php bin/magento cache:flush`

### Type 2: Composer

- Install the module composer by running `composer require ictmasterpk/module-pricemeterproductssync`
- enable the module by running `php bin/magento module:enable IctMasterPk_PricemeterProductsSync`
- apply database updates by running `php bin/magento setup:upgrade`\*
- Flush the cache by running `php bin/magento cache:flush`


## Configuration

- Price Meter API Token

## Features

- With auto sync, whenever you create, update or delete your product it automatically updates your product on Price Meter without any further action
- Update product keywords for Price Meter right from the products page
- For bulk update your products, just download CSV from Products page by clicking "Price Meter Export Products" and upload it on Price Meter
