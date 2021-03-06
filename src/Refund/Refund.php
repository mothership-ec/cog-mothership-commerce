<?php

namespace Message\Mothership\Commerce\Refund;

use Message\Cog\ValueObject\Authorship;
use Message\Mothership\Commerce\Order\Transaction\RecordInterface;

/**
 * Model defining a refund.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Refund implements RecordInterface
{
	const RECORD_TYPE = 'refund';

	public $id;
	public $authorship;
	public $payment;
	public $currencyID;
	public $method;
	public $amount;
	public $reason;
	public $reference;

	/**
	 * @var float
	 */
	private $_tax;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->authorship = new Authorship;

		$this->authorship
			->disableUpdate();
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

	/**
	 * @param $taxAmount
	 */
	public function setTax($taxAmount)
	{
		$this->_tax = (float) $taxAmount;
	}

	/**
	 * @return float | null
	 */
	public function getTax()
	{
		return $this->_tax;
	}
}