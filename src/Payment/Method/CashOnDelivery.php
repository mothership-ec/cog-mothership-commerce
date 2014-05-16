<?php

namespace Message\Mothership\Commerce\Payment\Method;

use Message\Mothership\Commerce\Payment\MethodInterface;

/**
 * CashOnDelivery payment method.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class CashOnDelivery implements MethodInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'cash-on-delivery';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayName()
	{
		return 'Cash On Delivery';
	}
}