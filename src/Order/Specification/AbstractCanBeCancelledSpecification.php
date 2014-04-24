<?php

namespace Message\Mothership\Commerce\Order\Specification;

use Message\Mothership\Commerce\Order\Statuses;
use Message\Cog\Event\DispatcherInterface;

/**
 * Abstract class defining whether an object can be cancelled or not.
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
abstract class AbstractCanBeCancelledSpecification
{
	protected function _checkStatus($statusCode)
	{
		return (Statuses::AWAITING_DISPATCH === $statusCode || Statuses::PROCESSING === $statusCode);
	}
}
