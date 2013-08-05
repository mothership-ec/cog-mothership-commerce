<?php

namespace Message\Mothership\Commerce\Controller\Order\Details;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;

class Metadata extends Controller
{
	protected $_order;

	public function summary($orderId)
	{
		$this->_order = $this->get('order.loader')->getById($orderId);

		return $this->render('Message:Mothership:Commerce::order:details:metadata:summary', array(
			'metadata' => $this->_order->metadata,
			'order' => $this->_order,
		));
	}
}
