<?php

namespace Message\Mothership\Commerce\Order\Entity\Payment;

use Message\Mothership\Commerce\Order\Entity\EntityInterface;

use Message\Cog\ValueObject\Authorship;

use Message\Mothership\Commerce\Order\Transaction\RecordInterface;

/**
 * Represents a payment on an order.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Payment implements EntityInterface, RecordInterface
{
	const RECORD_TYPE = 'payment';

	public $id;
	public $authorship;
	public $order;
	public $return;
	public $method;
	public $amount;
	public $change;
	public $reference;

	public function __construct()
	{
		$this->authorship = new Authorship;

		$this->authorship
			->disableUpdate();
	}

	/**
	 * Get the amount tendered for this transaction. This is the actual amount
	 * given to the merchant with the payment (amount + change).
	 *
	 * @return float
	 */
	public function getTenderedAmount()
	{
		return $this->amount + ($this->change ?: 0);
	}

	/**
	 * Get a reference for the payment that is suitable for the customer.
	 *
	 * @todo   Remove the direct mention of sagepay here, it should pass it to
	 *         the sagepay module to determine the reference.
	 *
	 * @return string
	 */
	public function getCustomerFacingReference()
	{
		if ('sagepay' == $this->method->getName()) {
			$reference = json_decode($this->reference);

			return isset($reference->VPSTxId) ? $reference->VPSTxId : '';
		}

		return $this->reference;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRecordType()
	{
		return self::RECORD_TYPE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRecordID()
	{
		return $this->id;
	}
}