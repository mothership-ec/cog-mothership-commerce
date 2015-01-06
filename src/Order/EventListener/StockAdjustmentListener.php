<?php

namespace Message\Mothership\Commerce\Order\EventListener;

use Message\Mothership\Commerce\Order\Events as OrderEvents;
use Message\Mothership\Commerce\Order\Event;

use Message\Cog\Event\EventListener as BaseListener;
use Message\Cog\Event\SubscriberInterface;

class StockAdjustmentListener extends BaseListener implements SubscriberInterface
{
	/**
	 * {@inheritdoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(
			OrderEvents::CREATE_END => array(
				array('adjustStock'),
			),
		);
	}

	public function adjustStock(Event\TransactionalEvent $event)
	{
		$order = $event->getOrder();
		$trans = $event->getTransaction();

		$stockManager = $this->get('stock.manager');
		$stockManager->setTransaction($trans);
		$stockManager->createWithRawNote(true);

		$stockManager->setReason($this->get('stock.movement.reasons')->get('new_order'));
		
		$trans->add("SET @STOCK_NOTE = CONCAT('Order #', ?i);", $order->id);
		$stockManager->setNote('@STOCK_NOTE');
		$stockManager->setAutomated(true);

		foreach($order->getItems() as $item) {
			$stockManager->decrement($item->getUnit(), $item->stockLocation);
		}
	}
}