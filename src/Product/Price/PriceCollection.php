<?php 

namespace Message\Mothership\Commerce\Product\Price;

use Message\Cog\ValueObject\Collection as BaseCollecion;

class PriceCollection extends BaseCollecion
{
	protected function _configure()
	{
		// ensure we add only TypedPrice with allowed types
		$this->setType('Message\\Mothership\\Commerce\\Product\\Price\\TypedPrice');

		// key is type
		$this->setKey('type');
	}
}