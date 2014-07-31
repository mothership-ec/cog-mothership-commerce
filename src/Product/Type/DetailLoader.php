<?php

namespace Message\Mothership\Commerce\Product\Type;

use Message\Cog\DB\Query;
use Message\Cog\Field;
use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\ProductEntityLoaderInterface;
use Message\Mothership\Commerce\Product\Loader as ProductLoader;

class DetailLoader implements ProductEntityLoaderInterface
{
	protected $_query;
	protected $_fieldFactory;
	protected $_types;
	protected $_productLoader;

	public function __construct(Query $query, Field\Factory $factory, Collection $types)
	{
		$this->_query        = $query;
		$this->_fieldFactory = $factory;
		$this->_types        = $types;
	}

	/**
	 * @{inheritdocs}
	 */
	public function setProductLoader(ProductLoader $productLoader)
	{
		$this->_productLoader = $productLoader;

		return $this;
	}

	/**
	 * Gets details by product
	 * @param  Product $product Product to load details for
	 * @return array            array of details
	 */
	public function getByProduct(Product $product)
	{
		return $this->load($product);
	}

	/**
	 * only public for BC
	 * @see getByProduct()
	 */
	public function load(Product $product)
	{
		$result = $this->_query->run("
			SELECT
				product_id AS productID,
				name       AS field,
				value,
				value_int,
				locale
			FROM
				product_detail
			WHERE
				product_id = :productID?i
		", array(
			'productID'	=> $product->id,
		));

		$details = new Details;

		$type    = $this->_types->get($product->type);

		$this->_fieldFactory->build($type);

		foreach ($this->_fieldFactory as $name => $field) {
			$details->$name	= $field;
		}

		foreach ($result as $row) {
			$field = $details->{$row->field};

			if ($field instanceof Field\BaseField) {
				$field->setValue($row->value);
			}
			else {
				continue;
			}
		}

		return $details;
	}
}