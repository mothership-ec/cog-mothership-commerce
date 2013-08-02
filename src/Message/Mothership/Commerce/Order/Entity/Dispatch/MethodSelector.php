<?php

namespace Message\Mothership\Commerce\Order\Entity\Dispatch;

use Message\Mothership\Commerce\Order\Order;

/**
 * Determines which dispatch method to use for a specific order by allowing the
 * installation to define a closure for this logic.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class MethodSelector
{
	protected $_collection;
	protected $_decisionFunction;

	/**
	 * Constructor.
	 *
	 * @param MethodCollection $collection The collection of dispatch methods
	 */
	public function __construct(MethodCollection $collection)
	{
		$this->_collection = $collection;
	}

	/**
	 * Set the closure that defines the logic for determining which dispatch
	 * method to use for a specific order.
	 *
	 * This closure will be provided with one argument, which will be an instance
	 * of the order in question.
	 *
	 * @param \Closure $decisionFunction The decision function
	 */
	public function setFunction(\Closure $decisionFunction)
	{
		$this->_decisionFunction = $decisionFunction;
	}

	/**
	 * Select the dispatch method to use for a specific order.
	 *
	 * @param  Order $order    The order to get the dispatch method for
	 *
	 * @return MethodInterface The appropriate dispatch method
	 */
	public function getMethod(Order $order)
	{
		if (!$this->_decisionFunction) {
			throw new \RuntimeException('Cannot determine dispatch method: decision function has not been defined');
		}

		$function   = $this->_decisionFunction;
		$methodName = $function($order);
		$method     = $this->_collection->get($methodName);

		return $method;
	}

	/**
	 * Get a new instance of `Dispatch` with the method and order set for a
	 * specific order.
	 *
	 * @param  Order  $order The order to get a dispatch for
	 *
	 * @return Dispatch      The dispatch with the method & order set
	 */
	public function getDispatch(Order $order)
	{
		$method = $this->getMethod($order);

		$dispatch = new Dispatch;
		$dispatch->method = $method;
		$dispatch->order  = $order;

		return $dispatch;
	}
}