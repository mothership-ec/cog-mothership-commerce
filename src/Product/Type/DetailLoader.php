<?php

namespace Message\Mothership\Commerce\Product\Type;

use Message\Cog\DB\Query;
use Message\Cog\Field;
use Message\Mothership\Commerce\Product\Product;

class DetailLoader
{
	protected $_query;
	protected $_fieldFactory;

	public function __construct(Query $query, Field\Factory $factory)
	{
		$this->_query			= $query;
		$this->_fieldFactory	= $factory;
	}

	public function load(Product $product)
	{
		$result =	$this->_query->run("
			SELECT
				product_id	AS productID,
				name		AS field,
				value,
				value_int,
				locale,
			FROM
				product_detail
			WHERE
				product_id	= :productID?i
		", array(
			'productID'	=> $product->id,
		));

		$details	= new Details;

		$this->_fieldFactory->build($product->type);

		foreach ($this->_fieldFactory as $name => $field) {
			$product->$name	= $field;
		}

		foreach ($result->flatten() as $row) {
			$field	= $details->{$row->field};

			if ($field instanceof Field\BaseField) {
				$field->setValue($row->value);
			}
			else {
				continue;
			}
		}

		$details->setValidator($this->_fieldFactory->getValidator());

		return $details;
	}
}