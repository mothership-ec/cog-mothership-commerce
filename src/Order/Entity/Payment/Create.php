<?php

namespace Message\Mothership\Commerce\Order\Entity\Payment;

use Message\Mothership\Commerce\Order;
use Message\Mothership\Commerce\Payment\Create as BaseCreate;

use Message\Cog\DB;
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
	protected $_paymentCreate;
	protected $_transOverridden = false;

	public function __construct(
		DB\Transaction $query,
		BaseCreate $paymentCreate,
		Loader $loader,
		DispatcherInterface $eventDispatcher
	)
	{
		$this->_trans           = $query;
		$this->_loader          = $loader;
		$this->_eventDispatcher = $eventDispatcher;
		$this->_paymentCreate   = $paymentCreate;

		// Set the base payment creator to use the same transaction
		$this->_paymentCreate->setTransaction($this->_trans);
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

		$this->_paymentCreate->setTransaction($this->_trans);

		return $this;
	}

	/**
	 * Creates a payment.
	 *
	 * Dispatches Order\Events::ENTITY_CREATE and Order\Events::ENTITY_CREATE_END
	 * events.
	 *
	 * Commits the transaction if $_transOverridden is false.
	 *
	 * @param  Payment $payment Payment to be created
	 *
	 * @return Payment          The created payment, reloaded if the transaction
	 *                          was not overridden
	 */
	public function create(Payment $payment)
	{
		$this->_validate($payment);

		$event = new Order\Event\EntityEvent($payment->order, $payment);
		$event->setTransaction($this->_trans);

		$payment = $this->_eventDispatcher->dispatch(
			Order\Events::ENTITY_CREATE,
			$event
		)->getEntity();

		if (!$payment->payment->id) {
			$this->_paymentCreate->create($payment->payment);
		}

		$this->_trans->run('
			INSERT INTO
				order_payment
			SET
				order_id   = :orderID?i,
				payment_id = :paymentID?i
		', [
			'orderID'   => $payment->order->id,
			'paymentID' => $payment->id,
		]);

		$event = new Order\Event\EntityEvent($payment->order, $payment);
		$event->setTransaction($this->_trans);

		$payment = $this->_eventDispatcher->dispatch(
			Order\Events::ENTITY_CREATE_END,
			$event
		)->getEntity();

		if (!$this->_transOverridden) {
			$this->_trans->commit();

			return $this->_loader->getByID($this->_trans->getIDVariable(str_replace('@', '', $payment->id)));
		}

		return $payment;
	}

	protected function _validate(Payment $payment)
	{
		if (!$payment->order) {
			throw new \InvalidArgumentException('Could not create payment: no order specified');
		}
	}
}