<?php

namespace Message\Mothership\Commerce\Product\Type\Detail;

class Collection implements \IteratorAggregate, \Countable
{
	protected $_details	= array();

	public function __construct(array $details = array())
	{
		foreach ($details as $detail) {
			if (!$detail instanceof Detail) {
				throw new \LogicException('Objects passed to Detail\\Collection must be an instance of Detail');
			}

			$this->_details[$detail->name] = $detail;
		}
	}

	public function get($name)
	{
		if ($this->exists[$name]) {
			return $this->_details[$name];
		}

		throw new \Exception('Not detail with name `'. $name . '` found!');
	}

	public function exists()
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