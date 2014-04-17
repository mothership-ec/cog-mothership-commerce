<?php

namespace Message\Mothership\Commerce\Controller\Order;

use Message\Cog\Controller\Controller;
use Message\Mothership\Commerce\Order;
use Message\Mothership\Commerce\Order\Entity\Note\Note;
use Message\Mothership\Commerce\Product\Stock\Movement\Reason\Reasons;
use Message\Mothership\Commerce\Product\Stock\Location;

class Cancel extends Controller
{
	protected $_order;
	protected $_tasks;

	public function cancelOrder($orderID)
	{
		$this->_order = $this->_getAndCheckOrder($orderID);
		$form = $this->createForm($this->get('order.form.cancel'));

		$form->handleRequest();

		if ($form->isValid()) {
			$stock          = $form->get('stock')->getData();
			$refund         = $form->get('refund')->getData();
			$notifyCustomer = $form->get('notifyCustomer')->getData();

			$transaction = $this->get('db.transaction');

			$orderEdit = $this->get('order.edit');
			$orderEdit->setTransaction($transaction);
			$this->_order = $orderEdit->updateStatus($this->_order, Order\Statuses::CANCELLED);
			$this->_tasks[] = 'cancelled order';

			if ($stock) {
				$this->_handleOrderStock($transaction);	
			}

			if ($refund) {
				// do the crazy refund stuff here
			}

			if ($transaction->commit()) {
				if ($notifyCustomer) {
					$this->_handleCustomerNotification('mail.factory.order.cancellation');
				}

				$this->addFlash('success', sprintf('Successfully:  %s.', join($this->_tasks, ', ')));
			}
		}

		return $this->render('Message:Mothership:Commerce::order:detail:cancel', [
			'order' => $this->_order,
			'form'  => $form,
			'title' => sprintf('Cancel Order #%s', $this->_order->id),
		]);
	}

	public function cancelItem($orderID, $itemID)
	{
		$this->_order = $this->_getAndCheckOrder($orderID);
		$this->_addresses = $this->get('order.address.loader')->getByOrder($this->_order);
		return $this->render('::order:detail:address:listing', array(
			'order' => $this->_order,
			'addresses' => $this->_addresses,
		));
	}

	protected function _handleOrderStock($transaction)
	{
		$this->_stockManager = $this->get('stock.manager');
		$this->_stockManager->setTransaction($transaction);

		$reason = $this->get('stock.movement.reasons')->get(Reasons::CANCELLED_ORDER);

		$stockLocation = $this->get('stock.locations')
			->getRoleLocation(Location\Collection::SELL_ROLE);

		$this->_stockManager->setReason($reason);
		$this->_stockManager->setNote(sprintf('Order #%s', $this->_order->id));
		$this->_stockManager->setAutomated(false);

		foreach ($this->_order->items->getRows() as $row) {
			$this->_stockManager->increment(
				$row->first()->getUnit(),
				$stockLocation,
				$row->getQuantity()
			);
		}

		$this->_tasks[] = sprintf('moved item(s) to stock location `%s`', $stockLocation->displayName);
	}

	protected function _handleCustomerNotification($factoryName)
	{
		$factory = $this->get($factoryName)
			->set('order', $this->_order);
		$this->get('mail.dispatcher')->send($factory->getMessage());
		
		$this->_tasks[] = 'notified customer';
	}

	protected function _getAndCheckOrder($orderID) {
		$order = $this->get('order.loader')->getById($orderID);

		if (!$order) {
			throw $this->createNotFoundException(
				$this->trans(
					'ms.commerce.order.feedback.general.failure.non-existing-order',
					array('%orderID%' => $orderID)
				),
				null,
				404
			);
		}

		return $order;
	}
}
