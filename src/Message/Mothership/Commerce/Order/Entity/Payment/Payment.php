<?php

namespace Message\Mothership\Commerce\Order\Entity\Payment;

use Message\Mothership\Commerce\Order\Entity\EntityInterface;

use Message\Cog\ValueObject\Authorship;

/**
 * Represents a payment on an order.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Payment implements EntityInterface
{
	public $id;
	public $authorship;
	public $order;
	public $return;
	public $method;
	public $amount;
	public $reference;

	public function __construct()
	{
		$this->authorship = new Authorship;

		$this->authorship
			->disableUpdate()
			->disableDelete();
	}

	public function getCustomerFacingReference()
	{
		if (strpos($this->reference,'sagepay')) {
			$reference = json_decode($this->reference);

			return isset($reference->VPSTxId) ? $reference->VPSTxId : '';
		}

		return $this->reference;
	}
}