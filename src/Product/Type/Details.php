<?php

namespace Message\Mothership\Commerce\Product\Type;

use Message\Cog\Field;
use Message\Cog\Validation\Validator;

class Details implements \IteratorAggregate, \Countable
{
	protected $_validator;
	protected $_details		= array();

	public function __construct($details = array())
	{
		foreach ($details as $detail) {
			if (!$detail instanceof Detail) {
				throw new \LogicException('Objects passed to Detail\\Collection must be an instance of Detail');
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
		return $this->exists($name) ? (string) $this->get($name) : null;
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

	public function flatten()
	{
		$details	= array();

		foreach ($this->all() as $name => $detail) {
			// Convert timestamp to \DateTime
			if ($this->_isDateOrTime($detail->dataType)) {
				$detail->value	 = new \DateTime(date('Y-m-d H:i:s', $detail->value));
			}
			$details[$name]	= $detail->value;
		}

		return $details;
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

	public function setValidator(Validator $validator)
	{
		$this->_validator	= $validator;
	}

	public function getValidator()
	{
		return $this->_validator;
	}
}