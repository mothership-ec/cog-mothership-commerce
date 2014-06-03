<?php

namespace Message\Mothership\Commerce\FieldType\Helper;

use Message\Cog\DB\Query;

/**
 * Share class for the purpose of optimising the loading of product lists.
 * If a page has multiple product drop downs, there is no point loading the same list of products multiple times so
 * this class means it only needs to be loaded once
 *
 * Class ProductList
 * @package Message\Mothership\Commerce\FieldType\Helper
 */
class ProductList
{
	/**
	 * @var \Message\Cog\DB\Query
	 */
	protected $_query;

	/**
	 * @var null | array
	 */
	protected $_productList;

	public function __construct(Query $query)
	{
		$this->_query = $query;
	}

	public function getProductList()
	{
		if (null === $this->_productList) {
			$this->_loadProductNames();
		}

		return $this->_productList;
	}

	protected function _loadProductNames()
	{
		$result = $this->_query->run("
			SELECT
				product_id,
				name
			FROM
				product
			WHERE
				deleted_at IS NULL
			ORDER BY
				name ASC
		");

		$this->_productList = [];

		foreach ($result as $product) {
			$this->_productList[$product->product_id] = $product->name;
		}
	}
}