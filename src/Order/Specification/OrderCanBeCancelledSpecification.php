<?php

namespace Message\Mothership\Commerce\Order\Specification;

use Message\Mothership\Commerce\Order\Order;

/**
 * Interface defining whether an order can be cancelled or not, using the
 * specification pattern.
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class OrderCanBeCancelledSpecification extends AbstractCanBeCancelledSpecification
{
	/**
	 * Check if the order statisfies this specification.
	 *
	 * @param  Order $order
	 * @return boolean
	 */
	public function isSatisfiedBy($order)
	{
		return $this->_checkStatus($order->status->code);
	}
}

