<?php

namespace Message\Mothership\Commerce\Product\Type;

use Message\Mothership\Commerce\Product\Product;
use Message\Cog\DB\Query;
use Message\Cog\Field\Factory;
use Symfony\Component\Validator\Constraints;

class ApparelProductType implements ProductTypeInterface
{
	protected $_query;
	protected $_seasons;

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
		$factory->add($factory->getField('text', 'year', 'Year'));

		$factory->add($factory->getField('datalist', 'season', 'Season')->setFieldOptions([
			'choices'     => $this->_getSeasons(),
		]));

		$factory->add($factory->getField('richtext', 'fabric', 'Fabric'));

		$factory->add($factory->getField('richtext', 'features', 'Features'));

		$factory->add($factory->getField('richtext', 'care_instructions', 'Care instructions'));

		$factory->add($factory->getField('richtext', 'sizing', 'Sizing'));
	}

	public function getProductDisplayName(Product $product)
	{
		return $product->details->brand . ' - ' . $product->name;
	}

	protected function _getSeasons()
	{
		if ($this->_seasons === NULL) {
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

			$this->_seasons = $result->flatten();
		}

		return $this->_seasons;
	}
}