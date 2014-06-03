<?php

namespace Message\Mothership\Commerce\Order\Entity\Payment\Method;

use Message\Mothership\Commerce\Order\Entity\Payment\MethodInterface;
use Message\Mothership\Commerce\Payment\Method\CashOnDelivery as BaseCashOnDelivery;

/**
 * CashOnDelivery payment method.
 *
 * @see Message\Mothership\Commerce\Payment\Method\CashOnDelivery
 *
 * @deprecated Left here for BC. To be removed.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class CashOnDelivery extends BaseCashOnDelivery implements MethodInterface
{
}