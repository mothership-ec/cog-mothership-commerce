<?php

namespace Message\Mothership\Commerce\Order\Transaction;

/**
 * Interface for loading decorators for records.
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
interface RecordLoaderInterface
{
	/**
	 * Returns record by its ID
	 *
	 * @return RecordInterface
	 */
	public function getByRecordID($id);
}