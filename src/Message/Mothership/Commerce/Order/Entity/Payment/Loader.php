<?php

namespace Message\Mothership\Commerce\Order\Entity\Payment;

use Message\Mothership\Commerce\Order\Entity;

/**
 * Order payment loader.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Loader implements Entity\LoaderInterface
{
	protected $_query;

	public function __construct(DB\Query $query)
	{
		$this->_query = $query;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getByOrder(Order $order)
	{
		$result = $this->_query->run('
			SELECT
				*
			FROM
				order_payment
			WHERE
				order_id = ?i
		', $order->id);
	}
}