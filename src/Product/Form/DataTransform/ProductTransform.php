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
use Message\Mothership\Commerce\Product\Tax\Strategy\TaxStrategyInterface;

class ProductTransform implements DataTransformerInterface
{
	private $_locale;
	private $_priceTypes;
	private $_defaultLocation;
	private $_productTypes;
	private $_defaultCurrency;
	private $_taxStrategy;

	public function __construct(
		Locale $locale,
		Location $defaultLocation,
		array $priceTypes = array(),
		ProductTypeCollection $productTypes,
		$defaultCurrency,
		TaxStrategyInterface $taxStrategy
	)
	{
		$this->_locale          = $locale;
		$this->_priceTypes      = $priceTypes;
		$this->_defaultLocation = $defaultLocation;
		$this->_productTypes    = $productTypes;
		$this->_defaultCurrency = $defaultCurrency;
		$this->_taxStrategy     = $taxStrategy;
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
			'description' => $product->getDescription(),
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
		$product = new Product($this->_locale, $this->_priceTypes, $this->_defaultCurrency, $this->_taxStrategy);

		$product->setName($data['name'])
			->setDisplayName($data['name'])
			->setBrand($data['brand'])
			->setCategory($data['category'])
			->setDescription($data['description'])
			->setType($this->_productTypes->get($data['type']))
		;

		// setting prices
		foreach($data['prices']['currencies'] as $currency => $typePrices) {
			foreach ($typePrices as $type => $price) {
				$product->setPrice($type, $currency, $price);
			}
		}

		// create the unit
		if (!empty($data['units'])){
			foreach($data['units'] as $unitData) {
				$unit = new Unit($this->_locale, $this->_priceTypes, $this->_defaultCurrency);
				$unit->setProduct($product);
				$unit->setSKU($unitData['sku']);
				$unit->setStockForLocation($unitData['stock'], $this->_defaultLocation);
				$price = $unitData['price'];

				foreach($unit->price as $type => $uPrice) {
					$unit->setPrice($price, $type);
				}

				$unit->setVisible(true);

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