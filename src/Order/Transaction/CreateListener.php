<?php

namespace Message\Mothership\Commerce\Order\Transaction;

use Message\Mothership\Commerce\Order\Events as OrderEvents;
use Message\Mothership\Commerce\Order\Event;
use Message\Mothership\Commerce\Order\Payment\Payment;
use Message\Mothership\Commerce\Order\Statuses;

use Message\Cog\Event\EventListener as BaseListener;

use Message\Cog\Event\SubscriberInterface;

/**
 * Event listener for transactions
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class CreateListener extends BaseListener implements SubscriberInterface
{
	protected $_attributes = array();
	protected $_transaction;

	/**
	 * {@inheritdoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(
			OrderEvents::CREATE_COMPLETE => array(
				array('orderCreated'),
			),
			OrderEvents::ENTITY_CREATE => array(
				array('entityCreated'),
			),
		);
	}

	public function setAttribute($name, $value)
	{
		$this->_attributes[$name] = $value;

		return $this;
	}

	public function removeAttribute($name)
	{
		if(isset($this->_attributes[$name])) {
			unset($this->_attributes[$name]);
		}

		return $this;
	}

	public function orderCreated(Event\Event $event)
	{
		$order = $event->getOrder();

		// magic number!
		if($order->status->code >= 0 || $order->status->code === Statuses::PENDING) {
			$this->_initTransaction();

			$this->_transaction->addRecord($order);

			foreach($order->items as $item) {
				$this->_transaction->addRecord($item);
			}

			foreach($order->payments as $payment) {
				$this->_transaction->addRecord($payment);
			}

			// get type from somewhere
			$this->_transaction->type = ($order->status->code === Statuses::PENDING ? 'contract_initiation' : 'order');

			$this->get('order.transaction.create')->create($this->_transaction);
		}
	}

	public function entityCreated(Event\EntityEvent $event)
	{
		$payment = $event->getEntity();
		if($payment instanceof Payment && $payment->getOrder()->status->code === Statuses::PENDING) {
			$this->_initTransaction();

			$this->_transaction->addRecord($payment);
			$this->_transaction->type = 'contract_payment';

			de($this->_transaction);
			$this->get('order.transaction.create')->create($this->_transaction);
		}
	}

	protected function _initTransaction()
	{
		$this->_transaction = new Transaction;
		$this->_transaction->attributes = $this->_attributes;
	}}