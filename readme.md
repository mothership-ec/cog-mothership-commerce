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
