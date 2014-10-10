<?php 

namespace Message\Mothership\Commerce\Product\Price;

use  Message\Cog\Localisation\Locale;

/**
 * This is an extension of pricing. It primatily exists to allow 
 * Pricing to be in a collection, using type property as an index
 */
class TypedPrice extends Pricing
{
	public $type;

	public function __construct($type, Locale $locale)
	{
		parent::__construct($locale);
		$this->type = $type;
	}

	/**
	 * gets the type
	 * @return string the type
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * Sets the type
	 * @param string $type type
	 */
	public function setType($type)
	{
		$this->type = $type;
	}
}