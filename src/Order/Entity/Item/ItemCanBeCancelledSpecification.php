<?php

namespace Message\Mothership\Commerce\Order\Entity\Item;

use Message\Mothership\Commerce\Order\Specification\AbstractCanBeCancelledSpecification;

/**
 * Interface defining whether an order can be cancelled or not, using the
 * specification pattern.
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class ItemCanBeCancelledSpecification extends AbstractCanBeCancelledSpecification
{
	public function isSatisfiedBy($item)
	{
		return $this->_checkStatus($item->status->code);
	}
}

