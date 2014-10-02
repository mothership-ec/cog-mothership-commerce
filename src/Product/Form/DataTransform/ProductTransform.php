<?php

namespace Message\Mothership\Commerce\Product\Form\DataTransform;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException as TransFailExeption;
use Message\Mothership\Commerce\Product\Product;
use Message\Cog\Localisation\Locale;
use Message\Mothership\Commerce\Product\Price\TypedPrice;

class ProductTransform implements DataTransformerInterface
{
	private $_locale;
	private $_priceTypes;

	public function __construct(Locale $locale, array $priceTypes = array())
	{
		$this->_locale     = $locale;
		$this->_priceTypes = $priceTypes;
	}

	/**
	 * @param Product $product                            Instance of Product object to convert to array
	 * @throws TransformationFailedException        Throws exception if Product object not given
	 *
	 * @return array                                Returns array of product segments
	 */
	public function transform($product)
	{
		if ($product === null) {
			return []; // For chaining, see symfony docs
		}

		if (!($product instanceof Product)) {
			throw new TransFailExeption("Parameter 1 must be instance of Message\Mothership\Commerce\Product\Product.");
		}

		$properties = [
			'name'        => $product->getName(),
			'brand'       => $product->getBrand(),
			'category'    => $product->getCategory(),
			'short_description' => $product->getShortDescription(),
		];

		foreach ($product->getPrices() as $key => $pricing) {
			$properties[$key . '_price'] = $product->getPrice($key);
		}

		return $properties;
	}

	/**
	 * @param string | array $array              Product data submitted by form
	 * @throws TransformationFailedException    Exception thrown if invalid type given
	 *
	 * @return Product                       Returns instance of Product object
	 */
	public function reverseTransform($array)
	{
		$product = new Product($this->_locale);

		$product->setName($array['name']);
		$product->setBrand($array['brand']);
		$product->setCategory($array['category']);
		$product->setShortDescription($array['short_description']);

		$prices = $product->getPrices();

		foreach ($this->_priceTypes as $type) {
			$price = new TypedPrice($type, $this->_locale);
			$price->setPrice('GBP', $array[$type . '_price'], $this->_locale);
			$prices->add($price);
		}

		return $product;
	}
}