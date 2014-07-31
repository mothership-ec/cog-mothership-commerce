<?php

namespace Message\Mothership\Commerce\Product\Type;

use Message\Cog\Field;
use Message\Cog\Validation\Validator;

class Details implements \IteratorAggregate, \Countable
{
	protected $_details		= array();

	public function __construct($details = array())
	{
		foreach ($details as $detail) {
			if (!$detail instanceof Field\FieldInterface) {
				throw new \LogicException('Objects passed to Details must be an instance of Field\FieldInterface');
			}

			$this->_details[$detail->name] = $detail;
		}
	}

	public function __set($var, $value)
	{
		if (!$value instanceof Field\FieldInterface) {
			throw new \InvalidArgumentException(sprintf(
				'Product detail must be an instance of FieldInterface, %s given',
				get_class($value)
			));
		}

		$this->_details[$var]	= $value;
	}

	public function __get($name)
	{
		return $this->exists($name) ? $this->get($name) : null;
	}

	public function __isset($name)
	{
		return isset($this->_details[$name]);
	}

	public function get($name)
	{
		return array_key_exists($name, $this->_details) ? $this->_details[$name] : null;
	}

	public function exists($name)
	{
		return isset($this->_details[$name]);
	}

	public function all()
	{
		return $this->_details;
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->_details);
	}

	public function count()
	{
		return count($this->_details);
	}

}