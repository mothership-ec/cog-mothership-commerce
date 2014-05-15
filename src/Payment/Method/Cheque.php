<?php

namespace Message\Mothership\Commerce\Payment\Method;

use Message\Mothership\Commerce\Payment\MethodInterface;

/**
 * Cheque payment method.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Cheque implements MethodInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'cheque';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayName()
	{
		return 'Cheque';
	}
}