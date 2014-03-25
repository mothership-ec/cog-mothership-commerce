<?php

namespace Message\Mothership\Commerce\Payable;

interface PayableInterface
{
	/**
	 * Get the amount to be paid.
	 *
	 * @return float
	 */
	public function getPayableAmount();

	/**
	 * Get the total value of the payable.
	 *
	 * @return float
	 */
	public function getPayableTotal();

	/**
	 * Get the currency with which to make the payment.
	 *
	 * @return string
	 */
	public function getPayableCurrency();

	/**
	 * Get an address associated with the payable.
	 *
	 * @param  string $type
	 * @return \Message\Mothership\Commerce\Address\Address
	 */
	public function getPayableAddress($type);
}