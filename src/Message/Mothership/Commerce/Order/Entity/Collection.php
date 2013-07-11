<?php

namespace Message\Mothership\Commerce\Order\Entity;

use Message\Mothership\Commerce\Order\Order;

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
		$this->_load();

		if (!$this->exists($id)) {
			throw new \InvalidArgumentException(sprintf('Identifier `%s` does not exist on entity collection', $id));
		}

		return $this->_items[$id];
	}

	public function exists($id)
	{
		$this->_load();

		return array_key_exists($id, $this->_items);
	}

	public function all()
	{
		$this->_load();

		return $this->_items;
	}

	public function count()
	{
		$this->_load();

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
		$this->_load();

		unset($this->_items[$id]);
	}

	public function getIterator()
	{
		$this->_load();

		return \ArrayIterator($this->_items);
	}

	protected function _load()
	{
		if (null === $this->_items) {
			$this->_items = $this->_loader->getByOrder($order) ?: array();

			return true;
		}

		return false;
	}
}