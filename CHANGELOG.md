# Changelog

## 5.19.0

- Improved validation on product creation when using product upload feature
- `Product\Upload\Exception\UploadFrontEndException` now extends `Message\Cog\Exception\TranslationLoginException`
- Exceptions thrown in `Product\Upload\ProductBuilder::build()` method will be rethrown as `UploadFrontEndException`, including original exception message
- `LogicException` thrown if a row in the product upload CSV contains data which fits into two separate product types
- Resolved issue in sales reports where voided orders were being included in dataset
- Resolved issue in sales reports where deleted orders were being included in dataset

## 5.18.2

- Fix issue where weights would not be saved correctly against units created using the product spreadsheet upload
- Product `$_authorship::update()` method called in `Product\Edit` instead of in controllers
- `Product\Unit\Edit::save()` no longer attempts to check affected rows if query object has been replaced with a database transaction object (as this results in fatal error), and simply returns the unit instead

## 5.18.1

- `Order\Loader::getBySearchTerms()` searches by metadata
- `Order\Loader::getBySearchTerms()` uses `USING` statements instead of `ON` statements in its query

## 5.18.0

- Add `getBySupplier()` method to product loader for loading products with a specific supplier reference
- Amend `mockery/mockery` requirement to be 0.9 instead of master

## 5.17.2

- Resolve issue where actual price would be unset on units after changing currency

## 5.17.1

- Resolved issue where *only* invisible units would be loaded by the unit loader if `Product\Unit\Loader::$_loadInvisible` is set to false.

## 5.17.0

- Resolved issue where units could be created with invalid unit options via the product CSV upload feature
- Resolved issue where refunds would not check which payment gateway to refund through (if E-commerce module is installed)
- Begin to remove coupling of E-commerce and commerce module with regards to refunding
- Added `Product\Upload\HeadingBuilder::getColumnDependencies()` method for determining which columns require a value in other columns of the spreadsheet
- Added `Product\Upload\HeadingKeys::setColumnDependencies()` method for setting which columns require a value in another column of the spreadsheet
- Added `Product\Upload\HeadingKeys::addColumnDependency()` method for adding a new column dependency to the spreadsheet
- Added `Product\Upload\HeadingKeys::getColumnDependencies()` method for getting the column dependencies for the spreadsheet
- Added `Order\Event\CancelEvent` event class to be fired when an order or item is cancelled
- Added `Order\Events::ORDER_CANCEL_REFUND` constant for labelling an event for a total order refund
- Added `Order\Events::ITEM_CANCEL_REFUND` constant for labelling an event for an item refund
- Added `gateway` service, which throws a `LogicException` if called (it should be overridden by `E-commerce`)
- Refactored `Controller\Order\Cancel\Cancel::cancelOrder()` method to use `CancelEvent` to allow for E-commerce module to process refunds if installed. If not installed, throws a deprecated error and defaults to old code
- Refactored `Controller\Order\Cancel\Cancel::cancelItem()` method to use `CancelEvent` to allow for E-commerce module to process refunds if installed. If not installed, throws a deprecated error and defaults to old code
- `Product\Upload\HeadingBuilder` sets variant values and names to depend on each other

## 5.16.2

- Resolve issue where unit weights would not default to product weight on load if not set

## 5.16.1

- Allow units to be priced at 0 when processing orders
- `Order\Entity\Item\Item::$actualPrice` defaults to null
- `Payment\Create::_validate()` now throws `InvalidArgumentException` if payment amount is less than 0 instead of less than or equal to 0
- Placeholder text on `value` field of `Field\OptionType` form field changed from **e.g. 'Red, Blue'** to **e.g. 'Red'**

## 5.16.0

- Added `Product\Barcode\CodeGenerator` namespace to allow for automated barcode generation based on the unit
- Added `Product\Barcode\CodeGenerator\GeneratorInterface` interface representing a barcode generator class
- Added `Product\Barcode\CodeGenerator\GeneratorCollection` collection class for storing registered barcode generators
- Added `Product\Barcode\CodeGenerator\AbstractGenerator` abstract class that implements `Product\Barcode\GeneratorInterface` to handle basic functionality for barcode generators
- Added `Product\Barcode\CodeGenerator\Code39Generator` class that extends `Product\Barcode\AbstractGenerator` for generating **CODE 39** style barcodes (in practice this means it uses the unit ID as the barcode)
- Added `Product\Barcode\CodeGenerator\Ean13Generator` class that extends `Product\Barcode\AbstractGenerator` for generating **EAN 13** style barcodes by generating a number based on the unit ID, a prefix number, and a single digit to pad it out with
- Added `Product\Barcode\CodeGenerator\Exception\BarcodeGenerationException` to be thrown when a barcode cannot be generated. This is caught by the `Controller\Product\Edit::processAddUnit()` controller, which will display a flash message informing the user that the unit has been created but a barcode could not be generated.
- Added `Product\Barcode\ValidTypes` class, containing static methods and properties for checking the validity of given barcode types
- Added `Product\Unit\BarcodeEdit` class for a saving unit barcodes without having to update the entire unit
- Added `EventListener::saveBarcode()` event listener to generate and save barcodes on unit creation
- Added `generator` option to `barcode.yml` fixture to determine which barcode generator to use. Defaults to `ean13`.
- Removed `barcode-type` option from `barcode.yml` and it can now be considered as deprecated
- Added `product.barcode.code_generator.collection` service which returns instance of `Product\Barcode\CodeGenerator\GeneratorCollection` with all registered barcode generators
- Added `product.barcode.code_generator.code39` service which returns instance of `Product\Barcode\CodeGenerator\Code39Generator`
- Added `product.barcode.code_generator.ean13` service which returns instance of `Product\Barcode\CodeGenerator\Ean13Generator`
- Added `product.barcode.code_generator` service which returns barcode generator taken from the collection. The generator is primarily determined by the `generator` config option in `barcode.yml`, if set it will return the generator with the name that matches that config. If `generator` is not set, it will check for the now deprecated `barcode-type` config, and return the first generator it finds that generates that type of barcode. If neither `generator` nor `barcode-type` are set in the config file, it will return the default generator, whose name is given to the `Product\Barcode\CodeGenerator\GeneratorCollection` constructor as a second parameter.
- Added `product.barcode.edit` service which returns instance of `Product\Unit\BarcodeEdit`
- `product.barcode.generate` service now uses the barcode type returned by the registered barcode generator to pass to its returned instance of `Commerce\Product\Barcode\Generate`
- `Controller\Product\Barcode::_getQuantities()` method now filters out units with a quantity of zero
- Migration to allow `barcode` column on `product_unit` table to be null
- Flash message displayed after unit creation
- Flash message displayed if barcodes cannot be generated for printing
- Product stock form no longer displays units that have invalid options
- `Product\Unit\Create::create()` now allows unit weights to be saved as `NULL`
- 'SKU' field on **Add unit** form is no longer a required field (defaults to unit ID)
- 'Weight' field on **Add unit** form is no longer a required field (defaults to `NULL`, inherits product weight)
- 'Option Name' and 'Option Value' fields are now required fields on **Add unit** form
- 'Option Name' and 'Option Value' fields now have placeholder text to make it clearer how they should be used
- Fixed `Order\Entity\Item\ItemTest` tests where they would not ignore the `Product\Unit\Unit::setProduct()` method
- Added unit tests for barcode generators
- Implement Travis
- Increased Cog dependency to 4.10

## 5.15.1

- Fix issue where barcode edit form would not autopopulate after an AJAX request

## 5.15.0

- Added ability to edit barcodes from the product unit edit screen
- No longer possible to create units with no options
- Added `Product\Form\UnitBarcode` form class for editing barcodes
- Added `Task\Product\DeleteOptionlessUnits` task for marking units with no options as deleted
- Added `Constraint\Product\UnitHasOptions` constraint (with validator `Constraint\Product\UnitHasOptionsValidator`) to apply to the `Product\Form\UnitEdit` form class to ensure that a unit has at least one option set
- Added `Product\Loader::getDefaultCurrency()` method for getting the default currency set against the product loader
- Added `product.form.unit.barcode` service which returns instance of `Product\Form\UnitBarcode`
- Added `Controller\Product\Edit::barcodeAction()` controller rendering and handling the barcode edit form
- Added `ms.commerce.product.unit.barcode` route which directs to `Controller\Product\Edit::barcodeAction()` if post data exists
- Added `product/modals/modal-barcode.html.twig` view file for displaying the barcode edit form
- Added `assets/js/unit-edit.js` javascript file for rendering barcode edit form and setting placeholder
- Added `gender` field to `Product\Type\ApparelProductType` for assigning gender to apparel products
- `Product\Unit\Create::create()` class throws exception if `Unit` parameter has no options set against it
- Unit edit form will not include units that have no options
- Resolve issue where if no options existed on unit the `options` parameter would be `['' => null]`
- `Product\Unit\Loader` uses revision ID as well as unit ID when joining unit options
- Altered 'Add SKU' text on unit create form to 'Add unit'

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
