<?php

namespace Message\Mothership\Commerce\Order\Entity\Payment;

use Message\User\UserInterface;

use Message\Mothership\Commerce\Order;

use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Cog\Event\DispatcherInterface;

/**
 * Order payment creator.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 * @author Iris Schaffer <iris@message.co.uk>
 */
class Create implements DB\TransactionalInterface
{
	protected $_trans;
	protected $_loader;
	protected $_eventDispatcher;
	protected $_currentUser;
	protected $_transOverridden = false;

	public function __construct(
		DB\Transaction $query,
		Loader $loader,
		DispatcherInterface $eventDispatcher,
		UserInterface $currentUser
	)
	{
		$this->_trans           = $query;
		$this->_loader          = $loader;
		$this->_eventDispatcher = $eventDispatcher;
		$this->_currentUser     = $currentUser;
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

		return $this;
	}

	/**
	 * Creates a payment. Dispatches Order\Events::ENTITY_CREATE and 
	 * Order\Events::ENTITY_CREATE_END events.
	 * Commits the transaction if $_transOverridden is false. 
	 * 
	 * @param  Payment $payment Payment to be persisted.
	 * @return Payment          Persisted payment. If transaction was committed,
	 *                          payment is reloaded by its ID.
	 */
	public function create(Payment $payment)
	{
		// Set create authorship data if not already set
		if (!$payment->authorship->createdAt()) {
			$payment->authorship->create(
				new DateTimeImmutable,
				$this->_currentUser->id
			);
		}

		$event = new Order\Event\EntityEvent($payment->order, $payment);
		$event->setTransaction($this->_trans);

		$payment = $this->_eventDispatcher->dispatch(
			Order\Events::ENTITY_CREATE,
			$event
		)->getEntity();

		$result = $this->_trans->run('
			INSERT INTO
				order_payment
			SET
				order_id   = :orderID?i,
				return_id  = :returnID?in,
				created_at = :createdAt?d,
				created_by = :createdBy?in,
				method     = :method?sn,
				amount     = :amount?f,
				`change`   = :change?fn,
				reference  = :reference?sn
		', array(
			'orderID'     => $payment->order->id,
			'returnID'    => $payment->return ? $payment->return->id : null,
			'createdAt'   => $payment->authorship->createdAt(),
			'createdBy'   => $payment->authorship->createdBy(),
			'method'      => $payment->method->getName(),
			'amount'      => $payment->amount,
			'change'      => $payment->change,
			'reference'   => $payment->reference,
		));

		$sqlVariable = 'PAYMENT_ID_' . spl_object_hash($payment);

		$this->_trans->setIDVariable($sqlVariable);
		$payment->id = '@' . $sqlVariable;

		$event = new Order\Event\EntityEvent($payment->order, $payment);
		$event->setTransaction($this->_trans);

		$payment = $this->_eventDispatcher->dispatch(
			Order\Events::ENTITY_CREATE_END,
			$event
		)->getEntity();

		if (!$this->_transOverridden) {
			$this->_trans->commit();

			return $this->_loader->getByID($this->_trans->getIDVariable($sqlVariable), $payment->order);
		}

		return $payment;
	}
}