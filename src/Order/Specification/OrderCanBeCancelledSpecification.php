<?php

namespace Message\Mothership\Commerce\Order\Specification;

/**
 * Interface defining whether an order can be cancelled or not, using the
 * specification pattern.
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class OrderCanBeCancelledSpecification extends AbstractCanBeCancelledSpecification
{
	public function isSatisfiedBy($order)
	{
		return $this->_checkStatus($order->status->code);
	}
}

