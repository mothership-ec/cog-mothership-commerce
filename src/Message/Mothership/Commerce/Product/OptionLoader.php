<?php

namespace Message\Mothership\Commerce\Product;

use Message\Cog\DB\Query;
use Message\Cog\DB\Result;
use Message\Cog\Localisation\Locale;


/**
 * Simple class for loading unit options out of the database
 */
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

	/**
	 * Return all the options for the given option name
	 *
	 * @param  string $type type to search for
	 *
	 * @return array        Array of results
	 */
	public function getByName($type)
	{
		$result = $this->_query->run(
			'SELECT
				option_value
			FROM
				product_unit_option
			WHERE
				option_name = ?s
			GROUP BY
				option_value',
			array(
				$type
			)
		);

		return $result->flatten();
	}
}