<?php

namespace Message\Mothership\Commerce\Payment\Method;

use Message\Mothership\Commerce\Payment\MethodInterface;

/**
 * PaymentOnPickup payment method.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class PaymentOnPickup implements MethodInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'payment-on-pickup';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayName()
	{
		return 'Payment On Pickup';
	}
}