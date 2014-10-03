<?php

namespace Message\Mothership\Commerce\Product\Form\DataTransform;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException as TransFailExeption;
use Message\Mothership\Commerce\Product\Product;
use Message\Cog\Localisation\Locale;
use Message\Mothership\Commerce\Product\Price\TypedPrice;
use Message\Mothership\Commerce\Product\Unit\Unit;
use Message\Mothership\Commerce\Product\Stock\Location\Location;
use Message\Mothership\Commerce\Product\Type\Collection as ProductTypeCollection;

class ProductTransform implements DataTransformerInterface
{
	private $_locale;
	private $_priceTypes;
	private $_defaultLocation;
	private $_productTypes;

	public function __construct(
		Locale $locale, 
		Location $defaultLocation, 
		array $priceTypes = array(), 
		ProductTypeCollection $productTypes)
	{
		$this->_locale          = $locale;
		$this->_priceTypes      = $priceTypes;
		$this->_defaultLocation = $defaultLocation;
		$this->_productTypes    = $productTypes;
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
			'prices'      => []
		];

		foreach ($product->getPrices() as $key => $pricing) {
			$properties['prices'][$key] = $product->getPrice($key);
		}


		/*
		 * TODO: Extend to enumerate $properties[units] fully
		 */

		return $properties;
	}

	/**
	 * @param string | array $data              Product data submitted by form
	 * @throws TransformationFailedException    Exception thrown if invalid type given
	 *
	 * @return Product                       Returns instance of Product object
	 */
	public function reverseTransform($data)
	{
		$product = new Product($this->_locale);

		$product->setName($data['name']);
		$product->setBrand($data['brand']);
		$product->setCategory($data['category']);
		$product->setShortDescription($data['short_description']);
		$product->setType($this->_productTypes->get($data['type']));

		// setting prices
		$prices = $product->getPrices();

		foreach ($this->_priceTypes as $type) {
			$price = new TypedPrice($type, $this->_locale);
			$price->setPrice('GBP', (isset($data['prices'][$type])?$data['prices'][$type]:0), $this->_locale);
			$prices->add($price);
		}

		// create the unit
		if (!empty($data['units'])){
			foreach($data['units'] as $unitData) {
				$unit = new Unit($this->_locale, $this->_priceTypes);
				$unit->id = $unitData['sku']; // id is set from db inset. use sku instead until stored in db.
				$unit->setProduct($product);
				$unit->setSKU($unitData['sku']);
				$unit->setStockForLocation($unitData['stock'], $this->_defaultLocation);
				$unit->setPrice($unitData['price']);

				$unit->revisionID = 1;

				foreach ($unitData['variants'] as $option) {
					$unit->setOption($option['key'], $option['value']);
				}
				$product->addUnit($unit);
			}
		}

		return $product;
	}
}