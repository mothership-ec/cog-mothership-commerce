<?php

namespace Message\Mothership\Commerce\Order\Entity\Item;

use Message\Mothership\Commerce\Order;

/**
 * Order item loader.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Loader implements Order\Entity\LoaderInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function getByOrder(Order\Order $order)
	{

	}
}