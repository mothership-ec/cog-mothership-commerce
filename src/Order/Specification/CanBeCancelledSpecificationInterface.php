<?php

namespace Message\Mothership\Commerce\Order\Specification;

/**
 * Interface defining whether an object can be cancelled or not, using the
 * specification pattern.
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
interface CanBeCancelledSpecificationInterface
{
	public function isSatisfiedBy($object);
}

