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

### Movements and Partial Movements
When loading movements using `Product\Stock\Movement\Loader`, Movements can either have all adjustments(for example when you load a movement by ID) or just a selection of adjustments which matches a certain criteria (for example when loading all movements and adjustments for a certain unit).
The movements which have all their adjustments set, are instances of `Product\Stock\Movement\Movement`, whereas the other ones are `Product\Stock\Movement\PartialMovement`s, which allows to differenciate the two (e.g. when debugging).
Partial movement extends movement and does not add any additional functionality, there is no difference between the two classes other than the name.

### The Stock Manager
`Product\Stock\StockManager` is a service providing an interface to adjust stock levels. The service is called 'stock.manager'.
It is responsible for both - saving stock movements and updating the actual stock level in the database on an transactional basis.
The stock manager internally has a `Product\Stock\Movement\Movement` which is filled with adjustments by using the following methods:
* `increment`: increments the stock level for given unit and location (by the provided value)
* `decrement`: decrements the stock level for given unit and location (by the provided value)
* `set`: sets the stock level for given unit and location to a specified value

Also, the stock manager has methods to set the movement's properties:
* `setReason`: Every movement MUST have a reason, see `Product\Stock\Movement\Reason` for details.
* `setNote`: Optional string (handy for storing e.g. order-ids on the movement)
* `setAutomated`: Whether the movement was generated automatically or not

Once all the adjustments are added to the stock manager, the changes can be changed by calling `commit()` which will then save all changes to a transaction and commit it.

### Movement\Iterator
The iterator is a class which iterates over stock movements for a set of given units. It is accessible through the service 'stock.movement.iterator'.
This allows us to go through the stock history for these units and get stock levels before and after every movement.
To set the units, there are two methods:
* `addUnits()`, which adds an array of units by calling
* `addUnit()`, which adds one unit and loads all movements of that unit

As the `Product\Stock\Movement\Iterator` implements `\Iterator`, it can be iterated through with foreach-loops.
The most important methods for displaying the stock changes when iterating over them are:
* `hasStock(unit, location)`: tells you whether there was an adjustment in stock for a specified unit and location in the current movement
* `getStockBefore(unit, location)`: returns the stock before the adjustments for the specified unit and location in the current movement
* `getStockAfter(unit, location)`: returns the stock after the adjustments for the specified unit and location in the current movement
* `getLastStock(unit, location)`: returns the last stock saved in the iterators internal counter or - if there has not been an adjustment for that unit and location yet - returns the current stock level

Moreover there is the `getStockForMovement(movement, unit, location)` method, which returns the stock level at the time of the given movement

## Todo

* Add `Product` field type for the CMS
	* This will require changes to how the CMS finds fields (currently it only looks within it's own cogule)
* Revisit product options storage in `order_item`
* Add comments to all columns in database tables

## Suggestions

* For backwards compatibility, we might need to add a static method to `Order\Order` to quickly get an order by ID without directly using the loader. Hopefully not, though!