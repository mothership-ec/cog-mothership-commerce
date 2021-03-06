<?php

namespace Message\Mothership\Commerce\Product\Type;

class Collection implements \IteratorAggregate, \Countable
{
	protected $_productTypes = array();

	public function __construct(array $productTypes = array())
	{
		foreach ($productTypes as $productType) {
			$this->add($productType);
		}
	}

	public function add(ProductTypeInterface $productType)
	{
		$this->_productTypes[$productType->getName()]	= $productType;

		return $this;
	}

	public function get($name)
	{
		if (!isset($this->_productTypes[$name])) {
			throw new \InvalidArgumentException(sprintf('Product type "%s" not set on collection', $name));
		}

		return $this->_productTypes[$name];
	}

	public function count()
	{
		return count($this->_productTypes);
	}

	public function getIterator()
	{
		return new \ArrayIterator($this->_productTypes);
	}

	public function getList()
	{
		$types	= array();

		foreach ($this->_productTypes as $name => $type) {
			$types[$name]	= $type->getDisplayName() . ' (' . $type->getDescription() . ')';
		}

		return $types;
	}

	public function getDefault()
	{
		if (empty($this->_productTypes)) {
			throw new \LogicException('No product types registered!');
		}

		foreach ($this->_productTypes as $type) {
			return $type;
		}
	}
}