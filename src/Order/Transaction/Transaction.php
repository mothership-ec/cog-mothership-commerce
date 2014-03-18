<?php

namespace Message\Mothership\Commerce\Order\Transaction;

use Message\Cog\ValueObject\Authorship;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Class representing an transaction.
 * A transaction is a collection of records of a certain type (e.g. returns, orders...)
 * at a certain branch and till.
 *
 * @todo voidedBy and voidedAt should probably be on an extended authorship
 *       see https://github.com/messagedigital/cog/issues/307
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class Transaction
{
	public $id;
	public $authorship;
	public $voidedAt;
	public $voidedBy;
	public $type;
	public $records;

	/**
	 * Additional information for the transaction
	 *
	 * @var array
	 */
	public $attributes = array();

	public function __construct()
	{
		$this->authorship = new Authorship;
		$this->authorship->disableUpdate();
		$this->authorship->disableDelete();

		$this->records = new RecordCollection;
	}

	/**
	 * Set the voided metadata.
	 *
	 * @param  DateTimeImmutable|null $datetime The date & time of voiding,
	 *                                          null to use current date & time
	 * @param  mixed                  $user     The user responsible
	 *
	 * @return Transaction                      Returns $this for chainability
	 *
	 * @todo   Move this to own decorator
	 *
	 */
	public function void(DateTimeImmutable $datetime = null, $user = null)
	{
		$this->voidedAt = $datetime ?: new DateTimeImmutable('now');
		$this->voidedBy = $user;

		$this->voidedAt = $this->voidedAt->setTimezone(new \DateTimeZone(date_default_timezone_get()));

		return $this;
	}

	/**
	 * Whether transaction has already been voided or not.
	 *
	 * @return boolean Whether transaction has been voided
	 */
	public function isVoided()
	{
		return ($this->voidedAt !== null);
	}

	/**
	 * Allows you to add custom attributes to the transaction.
	 *
	 * @param  string      $name  name of attribute
	 * @param  mixed       $value value of attribute
	 *
	 * @return Transaction $this for chainability
	 */
	public function setAttribute($name, $value)
	{
		$this->attributes[$name] = $value;

		return $this;
	}

	/**
	 * Removes attribute with name $name
	 *
	 * @param  string      $name name of attribute to be removed
	 *
	 * @return Transaction $this for chainability
	 */
	public function removeAttribute($name)
	{
		if (!isset($this->attributes[$name])) {
			unset($this->attributes[$name]);
		}

		return $this;
	}
}