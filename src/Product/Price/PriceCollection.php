<?php 

namespace Message\Mothership\Commerce\Product\Price;

use Message\Cog\ValueObject\Collection as BaseCollecion;

class PriceCollection extends BaseCollecion
{
	public function __construct($priceTypes)
	{
		// ensure we add only TypedPrice with allowed types
		$this->addValidator(function($val) use ($priceTypes) {
			if($val instanceof TypedPrice && in_array($val->getType(), $priceTypes)) {
				return true;
			}

			return false;
		});
		
		// no sort
		$this->setSort(function($a, $b) {
			return true;
		});

		// key is type
		$this->setKey('type');
	}
}