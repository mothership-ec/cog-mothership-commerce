# Mothership Commerce

The `Message\Mothership\Commerce` cogule provides base commerce functionality for Mothership. This forms part of the building blocks for both `ECommerce` and `Epos`.

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
			"message/cog-mothership-commerce": "~1.0"
		}
	}

## Product\Stock
### General
`Product\Stock` is responsible for handling stock changes. Every stock change is documented as a stock adjustment(`Product\Stock\Movement\Adjustment\Adjustment`). Stock adjustments created within the same action(creating a new order/ adjusting stock levels in one request) are surrounded by a stock movement(`Product\Stock\Movement\Movement`), to give them a reason, authorship, etc.

### Stock Manager
The stock manager `Product\Stock\StockManager` is responsible for creating and saving new stock movements (and adjustments). Please read the detailed readme of `Product\Stock` for more information!

### Movement Iterator
Also there is an Iterator for stock movements, which allows you to iterate over the stock history and get the stock level at any time before or after a movement.  Please read the detailed readme of `Product\Stock` for more information!

## Todo

* Add `Product` field type for the CMS
	* This will require changes to how the CMS finds fields (currently it only looks within it's own cogule)
* Revisit product options storage in `order_item`
* Add comments to all columns in database tables