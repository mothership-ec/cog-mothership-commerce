<?php 

namespace Message\Mothership\Commerce\Product\Price;

use Message\Cog\ValueObject\Collection as BaseCollecion;

class PriceCollection extends BaseCollecion
{
	public function __construct($priceTypes)
	{
		parent::__construct();

		// ensure we add only TypedPrice with allowed types
		$this->setType('Message\\Mothership\\Commerce\\Product\\Price\\TypedPrice');
		
		// no sort
		// $this->setSort(function($a, $b) {
		// 	return true;
		// });

		// key is type
		$this->setKey('type');
	}
}