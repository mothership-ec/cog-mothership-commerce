<?php

namespace Message\Mothership\Commerce\Product;

use Message\Cog\DB\Query;
use Message\Cog\DB\Result;
use Message\Cog\Localisation\Locale;

class OptionLoader
{
	protected $_query;
	protected $_locale;

	public function __construct(Query $query, Locale $locale)
	{
		$this->_query = $query;
		$this->_locale = $locale;
	}

	public function getAllOptionNames()
	{
		$result = $this->_query->run(
			'SELECT
				option_name
			FROM
				product_unit_option
			GROUP BY
				option_name'
		);

		return $result->flatten();
	}

	public function getAllOptionValues()
	{

		$result = $this->_query->run(
			'SELECT
				option_value
			FROM
				product_unit_option
			GROUP BY
				option_value'
		);

		return $result->flatten();

	}
}