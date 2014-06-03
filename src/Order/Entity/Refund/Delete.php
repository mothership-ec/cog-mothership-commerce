<?php

namespace Message\Mothership\Commerce\Order\Entity\Refund;

use Message\Mothership\Commerce\Refund\Delete as BaseDelete;

use Message\Cog\DB;

/**
 * Decorator for deleting order refunds.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Delete implements DB\TransactionalInterface
{
	protected $_refundDelete;

	/**
	 * Constructor
	 *
	 * @param BaseDelete $refundDelete Base refund delete decorator
	 */
	public function __construct(BaseDelete $refundDelete)
	{
		$this->_refundDelete = $refundDelete;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setTransaction(DB\Transaction $transaction)
	{
		$this->_refundDelete->setTransaction($transaction);

		return $this;
	}

	/**
	 * Delete an order refund.
	 *
	 * @see Message\Mothership\Commerce\Refund\Delete::delete
	 *
	 * @param  Refund $refund The refund to delete
	 *
	 * @return Refund          The deleted refund
	 */
	public function delete(Refund $refund)
	{
		$refund->refund = $this->_refundDelete->delete($refund->refund);

		return $refund;
	}
}