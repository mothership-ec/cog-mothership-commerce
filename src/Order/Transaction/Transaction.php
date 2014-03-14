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
	public $records = array();

	/**
	 * Additional information for the transaction
	 * @var array
	 */
	public $attributes = array();

	public function __construct()
	{
		$this->authorship = new Authorship;
		$this->authorship->disableUpdate();
		$this->authorship->disableDelete();
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

	public function isVoided()
	{
		return ($this->voidedAt !== null);
	}

	/**
	 * Adds a record to the transaction
	 * @param  RecordInterface           $record transaction to be added
	 * @throws \InvalidArgumentException         If there's already a record with
	 *                                           the same ID and type set on the
	 *                                           transaction
	 */
	public function addRecord(RecordInterface $record)
	{
		foreach ($this->records as $curRec) {
			if ($curRec->getRecordID() === $record->getRecordID() && $curRec->getRecordType() === $record->getRecordType()) {
				throw new \InvalidArgumentException(
					sprintf('The record with ID `%s` and record-type `%s` has already been added.', $record->getRecordID(), $record->getRecordType())
				);
			}
		}

		$this->records[] = $record;

		return $this;
	}

	/**
	 * Allows you to add custom attributes to the transaction.
	 *
	 * @param  string      $name  name of attribute
	 * @param  mixed       $value value of attribute
	 * @return Transaction $this for chainability
	 */
	public function setAttribute($name, $value)
	{
		$this->attributes[$name] = $value;

		return $this;
	}

	/**
	 * Removes attribute with name $name
	 * @param  string      $name name of attribute to be removed
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