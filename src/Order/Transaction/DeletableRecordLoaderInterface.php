<?php

namespace Message\Mothership\Commerce\Order\Transaction;

/**
 * Interface for loading decorators for deletable records.
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
interface DeletableRecordLoaderInterface extends RecordLoaderInterface
{
	/**
	 * Sets whether the loader should also load deleted records
	 *
	 * @param  bool                     $bool Whether loader should also load deleted
	 *                                        records. Defaults to true.
	 *                                        
	 * @return DeletableRecordInterface       DeletableRecordInterface in order to chain the methods
	 */
	public function includeDeleted($bool = true);
}