# Changelog

## 5.14.1

- Resolve issue where `Product\OptionLoader` would not load options in alphabetical order

## 5.14.0

- Added `ProductPageMapper\AbstractMapper::includeDeletedPages()` for including deleted pages when loading product pages
- Added `ProductPageMapper\AbstractMapper::includeUnpublishedPages()` for including unpublished pages when loading product pages
- Added `Events::PRODUCT_ADMIN_TAB_BUILD` constant to be called when building product tab menu in admin panel
- `Controller\Tabs::index()` now fires `Events::PRODUCT_ADMIN_TAB_BUILD` event
- Product tab menu now built using an event
- Stock Summary report includes columns for brand name and cost price
- Remove unnecessary extra parameter when calling `Product\Tax\Strategy\TaxStrategyInterface::getNetPrice()`
- `Product\Unit\Loader::getByID()` throws `\InvalidArgumentException` if revision ID is neither numeric nor null
- Resolve issue where `Product\Unit\Loader` would load the first revision instead of the most recent

## 5.13.0

- Added `Order\OrderOrder` class, which contains a set of constants to be used by the `Order\Loader` for setting the order in which orders should be loaded
- Added `orderBy()` method to `Order\Loader` which takes constants declared in `Order\OrderOrder`, to set the order in which orders should be loaded
- Added `orderBy()` method to `Pagination\OrderAdapter`, which calls `Order\Loader::orderBy()` with value given
- Added `getBySearchTerm()` method to `Order\Loader` which takes a string, and each word within that string must match with at least one of the following:
    - Order ID
    - User email address
    - Customer forename
    - Customer surname
    - Address lines 1-4
    - Address country
    - Address postcode
    - Address telephone number
    - Address town
    - Address state (full name, not code)
    - Address country (full name, not code)
    - Discount code
    - Dispatch ID
    - Dispatch code
    - Order note
    - Payment reference
- Added protected `_loadDeleted` property to `Product\Unit\Loader`, which is set to false by default. If it is set to false then deleted units will not be loaded
- Added `includeDeleted()` method to `Product\Unit\Loader` to allow deleted units to be loaded by setting `_loadDeleted` to true
- Reversed order in which orders are displayed in 'All orders' tab
- Reversed order in which orders are displayed in 'Shipped orders' tab
- `Controller\Order\Listing::searchAction()` controller now uses `Order\Loader::getBySearchTerm()` instead of `Order\Loader::getByID()` and `Order\Loader::getByTrackingCode()`
- Added translations for flash messages and heading when using search functionality
- Order view no longer has 'Back to orders' link in sidebar, instead the sidebar is the same as that in the order listings

## 5.12.0

- Added `Product\Barcode\Sheet\Size3x8` class for printing 24 barcodes on a 3 x 8 grid
- Added `Product\Unit\Event` class to fire unit related events
- Added `Product\Unit\Events` class for storing unit event names
- `Product\Unit\Create` class requires a third parameter on construction that implements `Symfony\Component\EventDispatcher\EventDispatcher`
- `Product\Unit\Create#create()` method fires event at start of creation process
- `Product\Unit\Create#create()` method fires event and end of creation process
- Set default value in `barcode.yml` fixture to '5x13'
- Barcode template appends `size-` to the sheetname when assigning a CSS class
- Added styling for 3x8 barcode printing

## 5.11.0

- Refactor `Product\Unit\Loader` to use `QueryBuilder` - all unit data is now loaded in one query and instances are built by looping over the results
- Added `Product\Unit\Loader::getAll()` method for loading all units
- Changed `Product\Unit\Loader::__construct()` first parameter to take `DB\QueryBuilderFactory` instead of `DB\Query`
- `Product\Unit\Loader` instanciates `EntityLoaderCollection` on construct
- `Product\Unit\Loader::includeOutOfStock()` method parameter is no longer required, defaults to true
- `Product\Unit\Loader::includeInvisible()` method parameter is no longer required, defaults to true
- `Product\Unit\Unit::product` renamed to `Product\Unit\Unit::_product` and made protected
- `Product\Unit\Unit::__set()` method calls `setProduct()` if `$name` is 'product'
- `Product\Unit\Unit::__get()` method calls `getProduct()` if `$name` is 'product'
- Added `Product\Unit\UnitProxy` class for lazy loading products
- `Product\ProductProxy` class drops lazy loaded data upon serialization
- `Report\StockSummary` calls `join()` instead of `leftJoin()` when joining onto the `product` and `unit_options` tables

## 5.10.2

- Removed empty `Product/Image/LoaderTest` test class

## 5.10.1

- Product listing in admin panel does not display images if there are over a thousand products

## 5.10.0

- Added `NO_DELIVERY_CODE_PREFIX` constant to `Order\Entity\Dispatch\Dispatch` class for flagging a dispatch as being non-existent (not saved as null for BC purposes)
- Added `getCode()` method to `Order\Entity\Dispatch\Dispatch` which returns the tracking code unless it is flagged as non-existent, in which case it returns null
- Tracking codes do not display on **Dispatches** tab under order overview if none is set
- `Product\Edit` class saves product type
- Added ability to change product type from **Attributes** tab of product edit screen
- Added EU countries to default tax configuration (VAT)

## 5.9.0

- Added `Product\Image\Assignor` class for assigning images to products
- Added `Product\Image\Exception\AssignmentException` to be thrown when an image cannot be assigned to a product
- Added `Product\Upload\ProductImageCreate` class, which uses the `Assignor` to assign images to the newly created products based on the data from the CSV, and then save it to the database
- Added `defaultImage` column to product upload CSV template
- Display flash messages from `Product\Image\Exception\AssignmentException` if image could not be assigned to product
- Alter `_1400150176_DecoupleRefundsFromOrders` migration to only create table if not exists
- Alter `_1400150176_DecoupleRefundsFromOrders` migration to use `INSERT IGNORE INTO` when porting data from old tables
- Alter `_1400163185_DecouplePaymentsFromOrders` migration to only create table if not exists
- Alter `_1400163185_DecouplePaymentsFromOrders` migration to use `INSERT IGNORE INTO` when porting data from old tables
- Added translations for product image upload
- Increment `cog-mothership-file-manager` dependency to 3.1

## 5.8.0

- Added `setType()` method to `Order\Entity\Discount\Discount` which takes a string to mark which type of discount the entity represents
- Added `getType()` method to `Order\Entity\Discount\Discount` for getting the type string
- Added `type` column to `order_discount` table
- Added `getTotal()` method to `Order\Entity\Discount\Collection`
- Basket total price takes discount into account
- Discount event listener calculates fixed discounts before calculating percentage discounts on items
- Added `getDiscountedPrice()` method to `Order\Entity\Item\Item` class which returns the actual price minus the discount
- Added `getTotalDiscountedPrice()` method to `Order\Entity\Item\Collection` class which returns the total discounted price of all items in the collection
- Added `getTotalNetPrice()` method to `Order\Entity\Item\Collection` class which returns the total net price of all items in the collection
- Set precedence on method calls in item event listener
- Set precedence on method calls in order event listener
- Fixed issue where tax deductions were made before discounts were calculated
- Fixed broken exception message in `Order\Entity\Item\Row`
- Changed text on exceptions on `Product\Product` class
- Recalculation of tax on `Order\EventListener\ValidateListener` and error added if total item tax does not match product tax on order

## 5.7.0

- Add `Sales by Unit` report, containing the following columns:
    - Product
    - Option
    - Currency
    - Net
    - Tax
    - Gross
    - Transactions
- Add `commerce.sales_by_unit` service which returns the `Report\SalesByUnit` report class
- Add `commerce.sales_by_unit` to `commerce.reports` service
- Removed nonsensical `Option` column from `Sales by Product`
- Renamed "Number Sold" column in `Sales by Product` report to "Transactions"
- Removed unnecessary forth parameter in `Report\AbstractSales` call to parent constructor
- Units form no longer automatically populates the `Weight` field with the product's weight if there is no unit weight set or if the unit weight matches the product weight

## 5.6.0

- Add `getSaleUnits()` method to unit loader
- `getOptionPrices()` method on `Product` object uses type and currency ID as part of key, to resolve issue where RRPs and retail prices were returning the same price

## 5.5.1

- `getUnit()` method on `ProductProxy` no longer excludes deleted or out of stock units

## 5.5.0

- Implemented product caching
- Added `product.cache` service, an instance of `Product\Collection`
- `product.cache` service given to `Product\Loader` constructor
- `Product\Loader` caches products upon initial load. If product exists in cache it will not be loaded from the database, but taken from the cache instead.
- Added `Product\Event` for events relating to changes to products
- Added `Message\Cog\Event\Dispatcher` instance to `Product\Edit` constructor to allow dispatching of events upon product save. Dispatched event triggers removal of the product from the cache.
- Added note informing users that spreadsheets for product upload must be encoded using UTF8
- Added error message to product upload if non-UTF8 spreadsheet is uploaded

## 5.4.0

- Added `getOptionPrice()` to `Product`. This method can return either the highest or lowest product unit price based on variant options defaulting to the lowest price.
- Added `getOptionPrices()` to `Product`. This method returns an array of prices for the product units with the given variant options.
- Added `getOptionPriceTo()` to `Product`. This method returns the highest product unit price based on variant options.
- Added `getOptionPriceTo()` to `Product`. This method returns the lowest product unit price based on variant options.
- Added `PriceNotFoundException` exception to `Product\Exception` namespace. 

## 5.3.1

- Revert change to `Order\Entity\Collection::remove()` as it was removing items from baskets

## 5.3.0

- Fix issue on `Order\Entity\Collection::remove()` method where entity would sometimes be cast as an integer and break
- Undeprecated `getItems()` method on `Order` class
- Refactor sales reports to share methods
- Fix issue where filters did nothing on `Sales by Location` reports
- Added `TaxRateNotFoundException` to `Product\Tax\Exception` namespace
- Added `UpdateFailedException` to `Order\Exception` namespace
- Added `UpdatedFailedEvent` to `Order\Event` namespace
- The `TaxResolver` class throws a `TaxRateNotFoundException` instead of `LogicException` if tax rate is not set in config
- The `TotalsListener` catches `TaxRateNotFoundException` instead of `LogicException` to revert to old method of tax calculation (VAT assumption)
- The `TotalsListener` catches `UpdateFailedException` and dispatches an `UpdateFailedEvent`

## 5.2.3

- Exceptions are caught more gracefully when creating a product via the CSV if a value cannot be set against a field on the product
- Added `invalidateRow()` method to `Product\Upload\Validate` for redeclaring a valid row of a CSV as invalid

## 5.2.2

- Fix issue where all products in dashboard are loaded on AJAX requests

## 5.2.1

- Updated CP requirement to 3.2

## 5.2.0

- Added functionality to see which product options are assigned to an image
- Added functionality to delete an image from a product
- Currency cookies are namespaced if set to `ms-commerce-currency` in the config file
- Fixed issue where the Payment screen in the order overview would break if there was a payment against a return

## 5.1.0

- Quantity selector available on product selector
- Deprecated `Message\Mothership\Commerce\Field\ProductUnitInStockOnlyChoiceType`, replaced with `Message\Mothership\Commerce\Form\Extension\Type\UnitChoice`
- Changed data type for IDs on product image table to accept strings, as IDs are MD5 hashes and not integers
- Improved markup for product selector

## 5.0.0

- Initial open source release
