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
The `Product\Stock` is responsible for handling stock changes.
Every change in stock level is documented as a stock movement(`Product\Stock\Movement\Movement`), which consits of stock adjustments(`Product\Stock\Movement\Adjustment\Adjustment`).
These adjustments hold a unit, stock location and the change in stock level (the difference and not the new value).
Every stock movement can have any amount of stock adjustments in any unit and stock location.

### The Stock Manager
`Product\Stock\StockManager` is a service providing an interface to adjust stock levels.
It is responsible for both - saving stock movements and updating the actual stock level in the database on an transactional basis.
The stock manager internally has a `Product\Stock\Movement\Movement` which is filled with adjustments by using the following methods:
* `increment`: increments the stock level for given unit and location (by the provided value)
* `decrement`: decrements the stock level for given unit and location (by the provided value)
* `set`: sets the stock level for given unit and location to a specified value

Also, the stock manager has methods to set the movement's properties:
* `setReason`
* `setNote`
* `setAutomated`

Once all the adjustments are added to the stock manager, the changes can be changed by calling `commit()` which will then save all changes to a transaction and commit it.


## Todo

* Add `Product` field type for the CMS
	* This will require changes to how the CMS finds fields (currently it only looks within it's own cogule)
* Add `Product` library
* Add `Gateway` interfaces & library
* Add stock & stock movements stuff
* Revisit product options storage in `order_item`
* Add comments to all columns in database tables
* Add all the Entities
* Add event listener to set discount amounts on items
* Implement stock locations on order item entity when ready

## Suggestions

* For backwards compatibility, we might need to add a static method to `Order\Order` to quickly get an order by ID without directly using the loader. Hopefully not, though!