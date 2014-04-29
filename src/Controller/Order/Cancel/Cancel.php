<?php

namespace Message\Mothership\Commerce\Controller\Order\Cancel;

use Message\Cog\Controller\Controller;
use Message\Mothership\Commerce\Order;
use Message\Mothership\Commerce\Order\Entity\Note\Note;
use Message\Mothership\Commerce\Product\Stock\Movement\Reason\Reasons;
use Message\Mothership\Commerce\Product\Stock\Location;
use Message\Mothership\Commerce\Form\Order\Cancel as CancelForm;

/**
 * Controller responsible for the cancellation of whole orders and individual
 * items of an order.
 */
class Cancel extends Controller
{
	protected $_order;

	/**
	 * Collects success flashes of different actions.
	 * 
	 * @var array[string]
	 */
	protected $_successFlashes = [];

	/**
	 * Method responsible for cancelling a whole order.
	 * 
	 * @param  int $orderID Order ID
	 * 
	 * @return Message\Cog\HTTP\Response Response
	 */
	public function cancelOrder($orderID)
	{
		$this->_order = $this->_getAndCheckOrder($orderID);

		if (!$this->get('order.specification.cancellable')->isSatisfiedBy($this->_order)) {
			throw new \InvalidArgumentException(sprintf('Order #%s cannot be cancelled.', $this->_order->id));
		}

		$form = $this->createForm($this->get('order.form.cancel'), null, [
			'action' => $this->generateUrl(
				'ms.commerce.order.cancel',
				['orderID' => $orderID]
			),
			CancelForm::STOCK_LABEL_OPTION => 'all outstanding items',
		]);

		$refundAmount = $this->_order->getPayableTotal();
		$cancelledItems = $this->_order->items->getByCurrentStatusCode(Order\Statuses::CANCELLED);

		foreach ($cancelledItems as $item) {
			$refundAmount -= $item->gross;
		}

		$form->handleRequest();

		if ($form->isValid()) {
			$stock          = $form->get('stock')->getData();
			$refund         = $form->get('refund')->getData();
			$notifyCustomer = $form->get('notifyCustomer')->getData();

			$transaction = $this->get('db.transaction');

			$orderEdit = $this->get('order.edit');
			$orderEdit->setTransaction($transaction);
			$orderEdit->updateStatus($this->_order, Order\Statuses::CANCELLED);
			$this->_successFlashes[] = sprintf('Successfully cancelled order #%s.', $this->_order->id);

			if ($stock) {
				$this->_configureStockManagerAndLocation($transaction, Reasons::CANCELLED_ORDER);	

				foreach ($this->_order->items->getRows() as $row) {
					$this->_stockManager->increment(
						$row->first()->getUnit(),
						$this->_stockLocation,
						$row->getQuantity()
					);
				}

				$this->_successFlashes[] = sprintf('Successfully moved item(s) to stock location `%s`.', $this->_stockLocation->displayName);
			}

			$payable = new Order\CancellationRefund($this->_order);

			if ($transaction->commit()) {
				if ($notifyCustomer) {
					$this->_sendCustomerNotification('mail.factory.order.cancellation');
				}

				$this->_addFlashes();

				if ($refund) {
					$payable = new Order\CancellationRefund($this->_order);

					$controller = 'Message:Mothership:Commerce::Controller:Order:Cancel:Refund';
					return $this->forward($this->get('gateway')->getRefundControllerReference(), [
						'payable'   => $payable,
						'reference' => $this->_getPaymentReference(),
						'stages'    => [
							'failure' => $controller . '#orderFailure',
							'success' => $controller . '#orderSuccess',
						],
					]);
				}

				return $this->redirectToRoute('ms.commerce.order.detail.view', [
					'orderID' => $this->_order->id,
				]);
			}
		}

		return $this->render('Message:Mothership:Commerce::order:detail:cancel:order', [
			'order'         => $this->_order,
			'form'          => $form,
			'refundAmount'  => $refundAmount,
			'title'         => 'Cancel Order',
		]);
	}

	/**
	 * Method responsible for cancelling an item in an order.
	 * 
	 * @param  int $orderID Order ID
	 * @param  int $itemID  Item ID
	 * 
	 * @return Message\Cog\HTTP\Response Response
	 */
	public function cancelItem($orderID, $itemID)
	{
		$this->_order = $this->_getAndCheckOrder($orderID);
		$item = $this->_order->items->get($itemID);

		if (!$this->get('order.item.specification.cancellable')->isSatisfiedBy($item)) {
			throw new \InvalidArgumentException(sprintf('Item `%s` cannot be cancelled.', $item->getDescription()));
		}

		$form = $this->createForm($this->get('order.form.cancel'), null, [
			'action' => $this->generateUrl(
				'ms.commerce.order.item.cancel',
				['orderID' => $orderID, 'itemID' => $itemID]
			),
			CancelForm::STOCK_LABEL_OPTION => 'item',
		]);

		$cancelledItems = $this->_order->items->getByCurrentStatusCode(Order\Statuses::CANCELLED);
		$lastUncancelledItem = 1 == ($this->_order->items->count() - count($cancelledItems));

		$form->handleRequest();

		if ($form->isValid()) {
			$stock          = $form->get('stock')->getData();
			$refund         = $form->get('refund')->getData();
			$notifyCustomer = $form->get('notifyCustomer')->getData();

			$transaction = $this->get('db.transaction');

			$itemEdit = $this->get('order.item.edit');
			$itemEdit->setTransaction($transaction);
			$itemEdit->updateStatus($item, Order\Statuses::CANCELLED);
			$this->_successFlashes[] = sprintf('Successfully cancelled item `%s`.', $item->getDescription());

			if ($stock) {
				$this->_configureStockManagerAndLocation($transaction, Reasons::CANCELLED_ITEM);
				$this->_stockManager->increment(
					$item->getUnit(),
					$this->_stockLocation
				);

				$this->_successFlashes[] = sprintf('Successfully moved item to stock location `%s`.', $this->_stockLocation->displayName);	
			}

			if ($transaction->commit()) {
				if ($notifyCustomer) {
					$this->_sendCustomerNotification('mail.factory.order.item.cancellation');
				}

				$this->_addFlashes();

				if ($refund) {
					$payable = new Order\CancellationRefund($this->_order);
					$payable->setPayableAmount($item->gross);

					$controller = 'Message:Mothership:Commerce::Controller:Order:Cancel:Refund';
					return $this->forward($this->get('gateway')->getRefundControllerReference(), [
						'payable'   => $payable,
						'reference' => $this->_getPaymentReference(),
						'stages'    => [
							'failure' => $controller . '#itemFailure',
							'success' => $controller . '#itemSuccess',
						],
					]);
				}

				return $this->redirectToRoute('ms.commerce.order.detail.view.items', [
					'orderID' => $this->_order->id,
				]);
			}
		}

		return $this->render('Message:Mothership:Commerce::order:detail:cancel:item', [
			'order'               => $this->_order,
			'item'                => $item,
			'form'                => $form,
			'refundAmount'        => $item->gross,
			'title'               => 'Cancel Item',
			'lastUncancelledItem' => $lastUncancelledItem,
		]);
	}

	/**
	 * Configures $_stockManager by setting its transaction, reason, note and
	 * automated properties. Also sets $_stockLocation and sets it to the
	 * sell stock location.
	 * 
	 * @param  Mothership\Cog\DB\Transaction $transaction Transaction to be used
	 * @param  string                        $reasonName  Name of reason to be set
	 * 
	 */
	protected function _configureStockManagerAndLocation($transaction, $reasonName)
	{
		$this->_stockLocation = $this->get('stock.locations')
			->getRoleLocation(Location\Collection::SELL_ROLE);

		$this->_stockManager = $this->get('stock.manager');
		$this->_stockManager->setTransaction($transaction);

		$reason = $this->get('stock.movement.reasons')->get($reasonName);
		$this->_stockManager->setReason($reason);

		$this->_stockManager->setNote(sprintf('Order #%s', $this->_order->id));
		$this->_stockManager->setAutomated(false);
	}

	/**
	 * Sends customer notification using factory with name $factoryName.
	 * 
	 * @param  string $factoryName Name of the factory used to create the email.
	 */
	protected function _sendCustomerNotification($factoryName)
	{
		$factory = $this->get($factoryName)
			->set('order', $this->_order);
		$this->get('mail.dispatcher')->send($factory->getMessage());
		
		$this->_successFlashes[] = 'Successfully notified customer.';
	}

	/**
	 *	Gets order by order id.
	 * 
	 * @param  int                                                          $orderID Order ID
	 * @throws Symfony\Component\HttpKernel\Exception\NotFoundHttpException          If no order for id exists
	 * 
	 * @return Order\Order  Order with ID $orderID
	 */
	protected function _getAndCheckOrder($orderID)
	{
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

	/**
	 * Collects flashes, so there can't be flash weirdness, when something goes wrong
	 * with transactions.
	 */
	protected function _addFlashes()
	{
		foreach ($this->_successFlashes as $flash) {
			$this->addFlash('success', $flash);
		}
	}

	/**
	 * For now just returns latest payment's reference. Should in future find
	 * the right payment.
	 * 
	 * @return string Payment Reference
	 */
	protected function _getPaymentReference()
	{
		$payment = null;
		
		foreach ($this->_order->payments as $p) {
			$payment = $p;
		}

		return ($payment ? $payment->reference : null);
	}
}
       