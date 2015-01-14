<?php

namespace Message\Mothership\Commerce\Refund;

/**
 * Interface RefundableInterface
 * @package Message\Mothership\Commerce\Refund
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 */
interface RefundableInterface
{
	public function setTax($tax);
	public function getTax();
}