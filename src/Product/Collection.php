<?php

namespace Message\Mothership\Commerce\Product;

use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\ProductEntityLoaderInterface;
use Message\Cog\ValueObject\Collection as BaseCollection;

/**
 * Collection of units
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class Collection extends BaseCollection
{
	protected function _configure()
	{
		$this->setType('Message\\Mothership\\Commerce\\Product\\Product');
		$this->setKey('id');
	}
}