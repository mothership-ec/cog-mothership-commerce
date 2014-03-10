# Mothership Commerce

The `Message\Mothership\Commerce` cogule provides base commerce functionality for Mothership. This forms part of the building blocks for both `ECommerce` and `EPOS`.

## Installation

Install this package using [Composer](http://getcomposer.org/). The package name is `message/cog-mothership-commerce`.

You will need to add Message's private package server to the `repositories` key in `composer.json`:

	{
		"repositories": [
			{
				"type": "composer",
				"url" : "http://packages.message.co.uk"
			}
		],
		"require": {
			"message/cog-mothership-commerce": "1.0.*"
		}
	}

## Product\Stock
### General
`Product\Stock` is responsible for handling stock changes. Every stock change is documented as a stock adjustment(`Product\Stock\Movement\Adjustment\Adjustment`). Stock adjustments created within the same action(creating a new order/ adjusting stock levels in one request) are surrounded by a stock movement(`Product\Stock\Movement\Movement`), to give them a reason, authorship, etc.

### Stock Manager
The stock manager `Product\Stock\StockManager` is responsible for creating and saving new stock movements (and adjustments). Please read the detailed readme of `Product\Stock` for more information!

### Movement Iterator
Also there is an Iterator for stock movements, which allows you to iterate over the stock history and get the stock level at any time before or after a movement.  Please read the detailed readme of `Product\Stock` for more information!

## Product Page Mapper

The commerce package ships with two implementations of the product page mapper: `SimpleMapper` and
`OptionCriteriaMapper`. By default the `SimpleMapper` is aliased to `product.page_mapper`.

#### Configuration

To enable the mapper to correctly relate products to pages you must set the valid values for
`product_content.field_name` and `product_content.group_name` for product pages. Additionally you should set the valid
page types. You can change these using:

```php
$services->extend('product.page_mapper', function($mapper, $c) {
	$mapper->setValidFieldNames('product');

	// Passing an array to either method will match against all values
	$mapper->setValidGroupNames(['product', 'showcase']);

	// Passing false to the group name will exclude pages within any group
	$mapper->setValidGroupNames(false);

	// Passing null or an empty array to the group name will match pages with
	// any or no group
	$mapper->setValidGroupNames([]);

	$mapper->setValidPageTypes(['product', 'strap']);

	return $mapper;
});
```

These default to:

- Field Names: `'product'`
- Group Names: `null`
- Page Types: `'product'`


### Simple Mapper

The simple mapper just matches a basic product to a page.

#### Usage

```php
// Find a page from a product
$page = $services['product.page_mapper']->getPageForProduct($product);

// Find a product from a page
$product = $services['product.page_mapper']->getProductForPage($page);
```


### Option Criteria Mapper

The option criteria mapper can additionally apply a filter for a specific product option, for example `['colour' => 'red']`. You can pass any number of options: `['colour' => 'red', 'size' => 'medium']`.

To enable the option criteria mapper you must alias it to the page mapper in your services:

```php
$services['product.page_mapper'] = $services->raw('product.page_mapper.option_criteria');
```

#### Usage

In addition to the previous methods, you can also call:

```php
// Find all pages from a product
$pages = $services['product.page_mapper']->getPagesForProduct($product, ['colour' => 'red']);

// Find a page from a unit
$page = $services['product.page_mapper']->getPageForProductUnit($unit);

// Find units from a page
$units = $services['product.page_mapper']->getProductUnitsForPage($page);
```


### Custom Mappers

When writing a custom mapper you should extend `AbstractMapper` to ensure compatibility.


### Filters

You can optionally pass in filter callbacks that are applied after the results are pulled from the database. Returning `false` from the callback will remove the object from the results.

#### Usage

```php
$services->extend('product.page_mapper', function($mapper, $c) {
	$mapper->addFilter(function($obj) {
		if ($obj instanceof Page) {
			return (false !== stristr($obj->title, "foo"));
		}
	});

	return $mapper;
});
```

## Todo

* Add `Product` field type for the CMS
	* This will require changes to how the CMS finds fields (currently it only looks within it's own cogule)
* Revisit product options storage in `order_item`
* Add comments to all columns in database tables