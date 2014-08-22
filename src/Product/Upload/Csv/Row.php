<?php
/**
 * Created by PhpStorm.
 * User: thomas
 * Date: 22/08/2014
 * Time: 12:50
 */

namespace Message\Mothership\Commerce\Product\Upload\Csv;

class Row implements \IteratorAggregate, \Countable
{
	/**
	 * @var array
	 */
	private $_columns;

	public function __construct(array $columns)
	{
		$this->_columns = $columns;
	}

	public function getColumns()
	{
		return $this->_columns;
	}

	/**
	 * {@inheritDoc}
	 */
	public function count()
	{
		return count($this->_columns);
	}


	/**
	 * {@inheritDoc}
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->_columns);
	}
}