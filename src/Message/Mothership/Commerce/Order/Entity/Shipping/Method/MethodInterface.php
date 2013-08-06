<?php

namespace Message\Mothership\Commerce\Order\Entity\Shipping\Method;

/**
 * Interface defining a shipping method.
 *
 */
interface MethodInterface
{
	/**
	 * Get the name for the shipping method used internally as an identifier.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Get the name for the shipping method that is suitable to be displayed to
	 * users.
	 *
	 * @return string
	 */
	public function getDisplayName();

	/**
	 * Get the price for this shipping method.
	 */
	public function getPrice();

	/**
	 * Determine whether the shipping method is available for an order.
	 *
	 * @return bool
	 */
	public function isAvailable();
}