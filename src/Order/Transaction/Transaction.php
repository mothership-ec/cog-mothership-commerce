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
	public $branch;
	public $till;
	public $records = array();

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
	 * @param  mixed $user                      The user responsible
	 *
	 * @return Authorship                       Returns $this for chainability
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

	public function addRecord(RecordInterface $record)
	{
		foreach($this->records as $curRec) {
			if($curRec->getID() === $record->getID() && $curRec->getRecordType() === $record->getRecordType()) {
				throw new \InvalidArgumentException(
					sprintf('The record with ID `%s` and record-type `%s` has already been added.', $record->getID(), $record->getRecordType())
				);
			}
		}

		$this->records[] = $record;
	}
}