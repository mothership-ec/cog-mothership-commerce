<?php

namespace Message\Mothership\Commerce\Order\Entity\Payment;

use Message\User\UserInterface;

use Message\Mothership\Commerce\Order;

use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Order payment creator.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Create implements DB\TransactionalInterface
{
	protected $_query;
	protected $_loader;
	protected $_currentUser;
	protected $_transOverriden = false;

	public function __construct(
		DB\Transaction $query,
		Loader $loader,
		DispatcherInterface $eventDispatcher,
		UserInterface $currentUser
	)
	{
		$this->_query           = $query;
		$this->_loader          = $loader;
		$this->_eventDispatcher = $eventDispatcher;
		$this->_currentUser     = $currentUser;
	}

	/**
	 * Sets transaction and sets $_transOverriden to true
	 * @param DBTransaction $trans transaction
	 */
	public function setTransaction(DB\Transaction $trans)
	{
		$this->_query = $trans;
		$this->_transOverriden = true;

		return $this;
	}

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
		$event->setTransaction($this->_query);

		$payment = $this->_eventDispatcher->dispatch(
			Order\Events::ENTITY_CREATE,
			$event
		)->getEntity();

		$result = $this->_query->run('
			INSERT INTO
				order_payment
			SET
				order_id   = :orderID?i,
				return_id  = :returnID?in,
				created_at = :createdAt?d,
				created_by = :createdBy?in,
				method     = :method?sn,
				amount     = :amount?f,
				reference  = :reference?sn
		', array(
			'orderID'     => $payment->order->id,
			'returnID'    => $payment->return ? $payment->return->id : null,
			'createdAt'   => $payment->authorship->createdAt(),
			'createdBy'   => $payment->authorship->createdBy(),
			'method'      => $payment->method->getName(),
			'amount'      => $payment->amount,
			'reference'   => $payment->reference,
		));


		$this->_query->setIDVariable('PAYMENT_ID');
		$payment->id = '@PAYMENT_ID';

		$event = new Order\Event\EntityEvent($payment->order, $payment);
		$event->setTransaction($this->_query);

		$payment = $this->_eventDispatcher->dispatch(
			Order\Events::ENTITY_CREATE_END,
			$event
		)->getEntity();

		if (!$this->_transOverriden) {
			$this->_query->commit();

			return $this->_loader->getByID($this->_query->getIDVariable('PAYMENT_ID'), $payment->order);
		}

		return $payment;
	}
}