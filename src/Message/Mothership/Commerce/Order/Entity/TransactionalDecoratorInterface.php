<?php

namespace Message\Mothership\Commerce\Order\Entity;

use Message\Cog\DB;

/**
 * Interface for entity decorators that can be transactional.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface TransactionalDecoratorInterface
{
	/**
	 *
	 */
	public function setTransaction(DB\Transaction $trans);
}