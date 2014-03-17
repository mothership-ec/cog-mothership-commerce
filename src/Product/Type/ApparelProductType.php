<?php

namespace Message\Mothership\Commerce\Product\Type;

use Message\Mothership\Commerce\Product\Product;
use Message\Cog\DB\Query;
use Message\Cog\Field\Factory;

class ApparelProductType implements ProductTypeInterface
{
	protected $_query;

	public function __construct(Query $query)
	{
		$this->_query = $query;
	}

	public function getName()
	{
		return 'apparel';
	}

	public function getDisplayName()
	{
		return 'Apparel product';
	}

	public function getDescription()
	{
		return 'A wearable item e.g. clothing';
	}

	public function setFields(Factory $factory)
	{
		$factory->add($factory->getField('datalist', 'brand', 'Brand')->setOptions([
			'choices' => $this->_getBrands(),
		]));

		$factory->add($factory->getField('text', 'year', 'Year'))
			->val()->optional();

		$factory->add($factory->getField('datalist', 'season', 'Season')->setOptions([
			'choices' => $this->_getSeasons()
		]))->val()->optional();

		$factory->add($factory->getField('richtext', 'fabric', 'Fabric'))
			->val()->optional();

		$factory->add($factory->getField('richtext', 'features', 'Features'))
			->val()->optional();

		$factory->add($factory->getField('richtext', 'care_instructions', 'Care instructions'))
			->val()->optional();

		$factory->add($factory->getField('richtext', 'sizing', 'Sizing'))
			->val()->optional();

		$factory->add($factory->getField('text', 'supplier_ref', 'Supplier reference'))
			->val()->optional();
	}

	public function getProductDisplayName(Product $product = null)
	{
		return $product->details->brand . ' - ' . $product->name;
	}

	protected function _getBrands()
	{
		$result	= $this->_query->run("
			SELECT DISTINCT
				value
			FROM
				product_detail
			WHERE
				name = :brand?s
		", array(
			'brand'  => 'brand',
		));

		return $result->flatten();
	}

	protected function _getSeasons()
	{
		$result	= $this->_query->run("
			SELECT DISTINCT
				value
			FROM
				product_detail
			WHERE
				name = :season?s
		", array(
			'season' => 'season',
		));

		return $result->flatten();
	}
}