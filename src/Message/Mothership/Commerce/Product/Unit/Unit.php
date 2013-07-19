<?php

namespace Message\Mothership\Commerce\Product\Unit;

use Message\Cog\Localisation\Locale;
use Message\Mothership\Commerce\Product\Pricing;

class Unit
{
	public $id;
	public $weight;
	public $price;
	public $sku;
	public $barcode;
	public $visible;

	public $stock = array(
		1 => 0,
		2 => 0,
		3 => 0,
		4 => 0,
	);

	public $options = array(

	);

	public function __construct(Locale $locale, array $priceTypes)
	{
		foreach ($priceTypes as $type) {
			$this->price[$type] = new Pricing($locale);
		}

	}

	public function getOption($type)
	{
		if (!isset($this->options[$type])) {
			throw new Exception(sprintf('Option %s doesn\'t exist on unitID %i', $type, $this->id));
		}

		return $this->options[$type];
	}
}