<?php

namespace Message\Mothership\Commerce\Order\Transaction;

/**
 * Interface for records
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
interface RecordInterface
{

	/**
	 * Returns type of record
	 * @return string type
	 */
	public function getRecordType();

	/**
	 * Returns id of record
	 * @return int
	 */
	public function getID();
}