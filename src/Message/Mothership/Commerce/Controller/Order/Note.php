<?php

namespace Message\Mothership\Commerce\Controller\Order;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;

class Note extends Controller
{
	protected $_order;
	protected $_notes;

	public function notes($orderID)
	{
		return $this->_loadOrderAndNotesAndRender($orderID, 'Message:Mothership:Commerce::order:detail:note:notes');
	}

	protected function _loadOrderAndNotesAndRender($orderID, $view)
	{
		$this->_order = $this->get('order.loader')->getById($orderID);
		$this->_notes = $this->get('order.note.loader')->getByOrder($this->_order);

		return $this->render($view, array(
			'notes' => $this->_notes,
			'order' => $this->_order,
		));
	}

}
