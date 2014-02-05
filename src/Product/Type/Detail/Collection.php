<?php

namespace Message\Mothership\Commerce\Product\Type\Detail;

class Collection implements \IteratorAggregate, \Countable
{
	protected $_details	= array();

	public function __construct($details = array())
	{
		foreach ($details as $detail) {
			if (!$detail instanceof Detail) {
				throw new \LogicException('Objects passed to Detail\\Collection must be an instance of Detail');
			}

			$this->_details[$detail->name] = $detail;
		}
	}

	public function __get($name)
	{
		return (string) $this->get($name);
	}

	public function get($name)
	{
		if ($this->exists($name)) {
			return $this->_details[$name];
		}

		throw new \Exception('No detail with name `'. $name . '` found!');
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

	protected function _isDateOrTime($data)
	{
		$dateTimeTypes	= array(
			'date',
			'time',
			'datetime',
		);

		return in_array($data, $dateTimeTypes);
	}
}