<?php

namespace Message\Mothership\Commerce\Order\Entity\Payment\Method;

use Message\Mothership\Commerce\Order\Entity\Payment\MethodInterface;
use Message\Mothership\Commerce\Payment\Method\PaymentOnPickup as BasePaymentOnPickup;

/**
 * PaymentOnPickup payment method.
 *
 * @see Message\Mothership\Commerce\Payment\Method\PaymentOnPickup
 *
 * @deprecated Left here for BC. To be removed.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class PaymentOnPickup extends BasePaymentOnPickup implements MethodInterface
{
}