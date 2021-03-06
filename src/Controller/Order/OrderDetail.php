<?php

namespace Message\Mothership\Commerce\Controller\Order;

use Message\Cog\Controller\Controller;
use Message\Mothership\Commerce\Order\Event\BuildOrderTabsEvent;
use Message\Mothership\Commerce\Order\Events;
use Message\Mothership\Commerce\Order\Entity\Note\Note;

class OrderDetail extends Controller
{
	protected $_order;
	protected $_addresses;
	protected $_items;
	protected $_metadata;
	protected $_payments;
	protected $_dispatches;
	protected $_notes;

	public function orderOverview($orderID)
	{
		$this->_order = $this->_getAndCheckOrder($orderID);
		$this->_addresses = $this->get('order.address.loader')->getByOrder($this->_order);
		$this->_items = $this->get('order.item.loader')->getByOrder($this->_order);
		$this->_metadata = $this->_order->metadata;

		return $this->render('::order:detail:order:overview', array(
			'order'     => $this->_order,
			'addresses' => $this->_addresses,
			'items'     => $this->_items,
			'metadata'  => $this->_metadata,
			'itemCancellationSpecification' => $this->get('order.item.specification.cancellable'),
		));
	}

	public function addressListing($orderID)
	{
		$this->_order = $this->_getAndCheckOrder($orderID);
		$this->_addresses = $this->get('order.address.loader')->getByOrder($this->_order);
		return $this->render('::order:detail:address:listing', array(
			'order' => $this->_order,
			'addresses' => $this->_addresses,
		));
	}

	public function itemListing($orderID)
	{
		$this->_order = $this->_getAndCheckOrder($orderID);
		$this->_items = $this->get('order.item.loader')->getByOrder($this->_order);

		$statuses = array();
		foreach($this->_items as $item) {
			$statuses[$item->id] = $this->get('order.item.status.loader')->getHistory($item);
		}

		return $this->render('::order:detail:item:listing', array(
			'order'           => $this->_order,
			'items'           => $this->_items,
			'statuses'        => $statuses,
			'itemCancellable' => $this->get('order.item.specification.cancellable'),
		));
	}

	public function paymentListing($orderID)
	{
		$this->_order = $this->_getAndCheckOrder($orderID);
		$this->_payments = $this->get('order.payment.loader')->getByOrder($this->_order);
		return $this->render('::order:detail:payment:listing', array(
			'order' => $this->_order,
			'payments' => $this->_payments,
		));
	}

	public function dispatchListing($orderID)
	{
		$this->_order = $this->_getAndCheckOrder($orderID);
		$this->_dispatches = $this->get('order.dispatch.loader')->getByOrder($this->_order);
		return $this->render('::order:detail:dispatch:listing', array(
			'order' => $this->_order,
			'dispatches' => $this->_dispatches,
		));
	}

	public function noteListing($orderID)
	{
		$this->_order = $this->_getAndCheckOrder($orderID);
		$this->_notes = $this->get('order.note.loader')->getByOrder($this->_order);

		$createNoteForm = $this->_createNoteForm($orderID);

		return $this->render('::order:detail:note:listing', array(
			'order'          => $this->_order,
			'notes'          => $this->_notes,
			'createNoteForm' => $createNoteForm,
		));
	}

	public function processNote($orderID)
	{
		$this->_order = $this->_getAndCheckOrder($orderID);
		$form = $this->_createNoteForm($orderID);

		if ($form->isValid() and $data = $form->getFilteredData()) {
			$note = new Note;
			$note->order = $this->_order;
			$note->note = $data['note'];

			// TODO 'order_view' should be defined somewhere!
			$note->raisedFrom = 'order_view';
			$note->customerNotified = $data['customer_notified'];

			$this->get('order.note.create')->create($note);

			$this->addFlash(
				'success',
				$this->trans('ms.commerce.order.feedback.create-note.success')
			);
		}

		return $this->redirectToRoute('ms.commerce.order.detail.view.notes', array(
			'orderID' => $orderID,
		));
	}

	public function documentListing($orderID)
	{
		$this->_order     = $this->_getAndCheckOrder($orderID);
		$this->_documents = $this->get('order.document.loader')->getByOrder($this->_order);
		return $this->render('::order:detail:document:listing', array(
			'order' => $this->_order,
			'documents' => $this->_documents,
		));
	}

	public function sidebar($orderID)
	{
		$this->_order = ($this->_order ?: $this->_getAndCheckOrder($orderID));

		return $this->render('Message:Mothership:Commerce::order:detail:sidebar', array(
			'order' => $this->_order,
		));
	}

	public function tabs($orderID)
	{
		$this->_order = ($this->_order ?: $this->_getAndCheckOrder($orderID));
		$isCancellable = $this->get('order.specification.cancellable')->isSatisfiedBy($this->_order);

		$event = new BuildOrderTabsEvent($this->_order);

		$this->get('event.dispatcher')->dispatch(
			Events::BUILD_ORDER_TABS,
			$event
		);

		$event->setClassOnCurrent($this->get('http.request.master'), 'active');

		return $this->render('Message:Mothership:Commerce::order:detail:tabs', array(
			'order'         => $event->getOrder(),
			'items'         => $event->getItems(),
			'isCancellable' => $isCancellable,
		));
	}

	protected function _createNoteForm($orderID)
	{
		$this->_order = ($this->_order ?: $this->_getAndCheckOrder($orderID));

		$form = $this->get('form');
		$form->setAction($this->generateUrl('ms.commerce.order.detail.process.notes', array(
			'orderID' => $orderID
		)));

		$form->add('note', 'textarea', $this->trans('ms.commerce.order.note.note'));

		$form->add('customer_notified', 'checkbox', $this->trans('ms.commerce.order.note.create.notify'))
			->val()->optional();

		return $form;
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
