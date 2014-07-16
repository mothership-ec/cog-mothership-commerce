<?php

namespace Message\Mothership\Commerce\Bootstrap;

use Message\Mothership\Commerce;

use Message\Cog\Bootstrap\EventsInterface;
use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;

class Events implements EventsInterface, ContainerAwareInterface
{
	protected $_services;

	public function setContainer(ContainerInterface $container)
	{
		$this->_services = $container;
	}

	public function registerEvents($dispatcher)
	{
		$dispatcher->addSubscriber(new Commerce\Order\EventListener\TotalsListener);
		$dispatcher->addSubscriber(new Commerce\Order\EventListener\ValidateListener);
		$dispatcher->addSubscriber(new Commerce\Order\EventListener\StockAdjustmentListener);
		$dispatcher->addSubscriber(new Commerce\Order\EventListener\StatusListener(
			$this->_services['order.statuses'],
			$this->_services['order.edit']
		));
		$dispatcher->addSubscriber(new Commerce\Order\EventListener\CancellationListener);
		$dispatcher->addSubscriber(new Commerce\Order\EventListener\NotificationListener);

		$dispatcher->addSubscriber($this->_services['order.listener.vat']);
		$dispatcher->addSubscriber($this->_services['order.listener.assembler.stock_check']);

		$dispatcher->addSubscriber(new Commerce\Order\Entity\Address\EventListener);
		$dispatcher->addSubscriber(new Commerce\Order\Entity\Discount\EventListener);
		$dispatcher->addSubscriber(new Commerce\Order\Entity\Item\EventListener(
			$this->_services['order.item.statuses']->get(0),
			$this->_services['order.item.edit']
		));
		$dispatcher->addSubscriber(new Commerce\Order\Entity\Note\EventListener);
		$dispatcher->addSubscriber(new Commerce\Order\Entity\Payment\EventListener\RemoveTemporaryIdListener);

		$dispatcher->addSubscriber(new Commerce\EventListener);

		$dispatcher->addSubscriber(new Commerce\Order\Transaction\EventListener\CreateListener);
		$dispatcher->addSubscriber(new Commerce\Order\Transaction\EventListener\VoidListener);

		$dispatcher->addSubscriber(new Commerce\Order\Basket\EventListener\PersistenceListener);
		$dispatcher->addSubscriber(new Commerce\Order\Basket\EventListener\AttachUserListener);
	}
}