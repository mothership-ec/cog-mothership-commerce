<?php

namespace Message\Mothership\Commerce\Payment;

/**
 * Interface defining a payment method.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface MethodInterface
{
	/**
	 * Get the name for the payment method used internally as an identifier.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Get the name for the payment method that is suitable to be displayed to
	 * users.
	 *
	 * @return string
	 */
	public function getDisplayName();
}