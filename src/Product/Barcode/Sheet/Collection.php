<?php

namespace Message\Mothership\Commerce\Product\Barcode\Sheet;

class Collection implements \ArrayAccess, \IteratorAggregate, \Countable
{
	protected $_sheets = [];

	public function getIterator()
	{
		return new \ArrayIterator($this->_sheets);
	}

	public function get($id)
	{
		if (!$this->exists($id)) {
			throw new \InvalidArgumentException(sprintf('Identifier `%s` does not exist on sheet collection', $id));
		}

		return $this->_sheets[$id];
	}

	public function count()
	{
		return count($this->_sheets);
	}

	public function exists($id)
	{
		return array_key_exists($id, $this->_sheets);
	}

	public function add(SheetInterface $sheet)
	{
		$this->_sheets[$sheet->getName()] = $sheet;
	}

	public function offsetSet($id, $value)
	{
		throw new \BadMethodCallException('Collection does not allow setting sheets using array access');
	}

	public function offsetGet($id)
	{
		return $this->get($id);
	}

	public function offsetExists($id)
	{
		return $this->exists($id);
	}

	public function offsetUnset($id)
	{
		unset($this->_sheets[$id]);
	}
}