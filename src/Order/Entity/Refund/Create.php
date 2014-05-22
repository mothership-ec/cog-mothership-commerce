<?php

namespace Message\Mothership\Commerce\Order\Entity\Refund;

use Message\Mothership\Commerce\Order;
use Message\Mothership\Commerce\Refund\Create as BaseCreate;

use Message\Cog\DB;
use Message\Cog\Event\DispatcherInterface;

/**
 * Order refund creator.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 * @author Iris Schaffer <iris@message.co.uk>
 */
class Create implements DB\TransactionalInterface
{
	protected $_trans;
	protected $_loader;
	protected $_eventDispatcher;
	protected $_refundCreate;
	protected $_transOverridden = false;

	public function __construct(
		DB\Transaction $query,
		BaseCreate $refundCreate,
		Loader $loader,
		DispatcherInterface $eventDispatcher
	)
	{
		$this->_trans           = $query;
		$this->_loader          = $loader;
		$this->_eventDispatcher = $eventDispatcher;
		$this->_refundCreate    = $refundCreate;

		// Set the base refund creator to use the same transaction
		$this->_refundCreate->setTransaction($this->_trans);
	}

	/**
	 * Sets transaction and sets $_transOverridden to true.
	 *
	 * @param  DB\Transaction $trans transaction
	 * @return Create                $this for chainability
	 */
	public function setTransaction(DB\Transaction $trans)
	{
		$this->_trans           = $trans;
		$this->_transOverridden = true;

		$this->_refundCreate->setTransaction($this->_trans);

		return $this;
	}

	/**
	 * Creates a refund.
	 *
	 * Dispatches Order\Events::ENTITY_CREATE and Order\Events::ENTITY_CREATE_END
	 * events.
	 *
	 * Commits the transaction if $_transOverridden is false.
	 *
	 * @param  Refund $refund Refund to be created
	 *
	 * @return Refund          The created refund, reloaded if the transaction
	 *                          was not overridden
	 */
	public function create(Refund $refund)
	{
		$this->_validate($refund);

		$event = new Order\Event\EntityEvent($refund->order, $refund);
		$event->setTransaction($this->_trans);

		$refund = $this->_eventDispatcher->dispatch(
			Order\Events::ENTITY_CREATE,
			$event
		)->getEntity();

		if (!$refund->refund->id) {
			$this->_refundCreate->create($refund->refund);
		}

		$this->_trans->run('
			INSERT INTO
				order_refund
			SET
				order_id  = :orderID?i,
				refund_id = :refundID?i
		', [
			'orderID'  => $refund->order->id,
			'refundID' => $refund->id,
		]);

		$event = new Order\Event\EntityEvent($refund->order, $refund);
		$event->setTransaction($this->_trans);

		$refund = $this->_eventDispatcher->dispatch(
			Order\Events::ENTITY_CREATE_END,
			$event
		)->getEntity();

		if (!$this->_transOverridden) {
			$this->_trans->commit();

			return $this->_loader->getByID($this->_trans->getIDVariable(str_replace('@', '', $refund->id)));
		}

		return $refund;
	}

	protected function _validate(Refund $refund)
	{
		if (!$refund->order) {
			throw new InvalidArgumentException('Could not create refund: no order specified');
		}

		if (!$refund->refund->currencyID) {
			$refund->refund->currencyID = $refund->order->currencyID;
		}

		if ($refund->order->currencyID !== $refund->refund->currencyID) {
			throw new \InvalidArgumentException(sprintf(
				'Could not create refund: currency ID (%s) must match order currency ID (%s)',
				$refund->refund->currencyID,
				$refund->order->currencyID
			));
		}
	}
}