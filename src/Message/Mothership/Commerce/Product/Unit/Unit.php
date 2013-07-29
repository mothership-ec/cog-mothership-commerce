<?php

namespace Message\Mothership\Commerce\Product\Unit;

use Message\Cog\Localisation\Locale;
use Message\Cog\ValueObject\Authorship;
use Message\Mothership\Commerce\Product\Pricing;

class Unit
{
	public $id;
	public $price;
	public $sku;
	public $barcode;
	public $visible;
	public $authorship;
	public $supplierRef;
	public $weight;
	public $revisionID;

	public $stock = array(
		1 => 0,
	);

	public $options = array(

	);

	public $product;

	protected $_locale;

    public function __clone() {
		foreach ($this->price as $name => $pricing) {
			$this->price[$name] = clone $pricing;
		}
    }

	public function __construct(Locale $locale, array $priceTypes)
	{
		$this->authorship = new Authorship;
		$this->_locale = $locale;
		foreach ($priceTypes as $type) {
			$this->price[$type] = new Pricing($locale);
		}

	}

	public function setOption($type, $value)
	{
		$this->options[$type] = $value;
	}

	public function getPrice($type = 'retail', $currencyID = 'GBP')
	{
		return $this->price[$type]->getPrice($currencyID, $this->_locale);
	}

	public function getOption($type)
	{
		if (!isset($this->options[$type])) {
			throw new \InvalidArgumentException(sprintf('Option %s doesn\'t exist on unitID %i', $type, $this->id));
		}

		return $this->options[$type];
	}
}