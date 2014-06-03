<?php

namespace Message\Mothership\Commerce\Order\Entity\Payment\Method;

use Message\Mothership\Commerce\Order\Entity\Payment\MethodInterface;
use Message\Mothership\Commerce\Payment\Method\Paypal as BasePaypal;

/**
 * Paypal payment method.
 *
 * @see Message\Mothership\Commerce\Payment\Method\Paypal
 *
 * @deprecated Left here for BC. To be removed.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Paypal extends BasePaypal implements MethodInterface
{
}