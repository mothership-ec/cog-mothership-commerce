<?php

namespace Message\Mothership\Commerce\Payment\Method;

use Message\Mothership\Commerce\Payment\MethodInterface;

/**
 * Paypal payment method.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Paypal implements MethodInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'paypal';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayName()
	{
		return 'Paypal';
	}
}