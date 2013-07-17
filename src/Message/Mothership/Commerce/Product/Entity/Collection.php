<?php

namespace Message\Mothership\Commerce\Product\Entity;

use Message\Mothership\Commerce\Product\Product;

/**
 * Collection of entities relating to a specific order.
 *
 * Entities are lazy loaded on demand.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Collection implements \ArrayAccess, \IteratorAggregate, \Countable
{
	protected $_order;
	protected $_loader;

	protected $_items = null;

	public function __construct(Order $order, LoaderInterface $loader)
	{
		$this->_order  = $order;
		$this->_loader = $loader;
	}

	public function get($id)
	{
		$this->load();

		if (!$this->exists($id)) {
			throw new \InvalidArgumentException(sprintf('Identifier `%s` does not exist on entity collection', $id));
		}

		return $this->_items[$id];
	}

	public function getByProperty($property, $value)
	{
		$this->load();

		$return = array();

		foreach ($this->_items as $id => $item) {
			if (property_exists($item, $property) && $item->{$property} == $value) {
				$return[$id] = $item;
			}
		}

		return $return;
	}

	public function exists($id)
	{
		$this->load();

		return array_key_exists($id, $this->_items);
	}

	public function all()
	{
		$this->load();

		return $this->_items;
	}

	public function count()
	{
		$this->load();

		return count($this->_items);
	}

	public function offsetSet($id, $value)
	{
		throw new \BadMethodCallException('`Entity\Collection` does not allow setting entities using array access');
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
		$this->load();

		unset($this->_items[$id]);
	}

	public function getIterator()
	{
		$this->load();

		return \ArrayIterator($this->_items);
	}

	public function load()
	{
		if (null === $this->_items) {
			$this->_items = $this->_loader->getByOrder($this->_order) ?: array();

			return true;
		}

		return false;
	}
}