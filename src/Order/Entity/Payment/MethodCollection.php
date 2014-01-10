<?php

namespace Message\Mothership\Commerce\Order\Entity\Payment;

/**
 * A container for all payment methods available to the system.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class MethodCollection implements \IteratorAggregate, \Countable
{
	protected $_methods = array();

	/**
	 * Constructor.
	 *
	 * @param array $pageTypes An array of payment methods
	 */
	public function __construct(array $methods = array())
	{
		foreach ($methods as $name => $method) {
			$this->add($method);
		}
	}

	/**
	 * Add a payment method to this collection.
	 *
	 * @param MethodInterface $method The payment method to add
	 *
	 * @return MethodCollection         Returns $this for chainability
	 *
	 * @throws \InvalidArgumentException  If a payment method with the same name
	 *                                    has already been set on this collection
	 */
	public function add(MethodInterface $method)
	{
		if (isset($this->methods[$method->getName()])) {
			throw new \InvalidArgumentException(sprintf('Payment method `%s` is already defined', $method->getName()));
		}

		$this->_methods[$method->getName()] = $method;

		return $this;
	}

	/**
	 * Get a payment method set on this collection by name.
	 *
	 * @param  string $name    The payment method name
	 *
	 * @return MethodInterface The payment method instance
	 *
	 * @throws \InvalidArgumentException If the payment method has not been set
	 */
	public function get($name)
	{
		if (!isset($this->_methods[$name])) {
			throw new \InvalidArgumentException(sprintf('Payment method `%s` not set on collection', $name));
		}

		return $this->_methods[$name];
	}

	/**
	 * Get the number of payment methods registered on this collection.
	 *
	 * @return int The number of payment methods registered
	 */
	public function count()
	{
		return count($this->_methods);
	}

	/**
	 * Get the iterator object to use for iterating over this class.
	 *
	 * @return \ArrayIterator An \ArrayIterator instance for the `_methods`
	 *                        property
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->_methods);
	}
}