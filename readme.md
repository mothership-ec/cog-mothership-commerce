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

## Suggestions

* For backwards compatibility, we might need to add a static method to `Order\Order` to quickly get an order by ID without directly using the loader. Hopefully not, though!