<?php

namespace Message\Mothership\Commerce\Order\Entity\Payment\Method;

use Message\Mothership\Commerce\Order\Entity\Payment\MethodInterface;
use Message\Mothership\Commerce\Payment\Method\Manual as BaseManual;

/**
 * Manual payment method.
 *
 * @see Message\Mothership\Commerce\Payment\Method\Manual
 *
 * @deprecated Left here for BC. To be removed.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Manual extends BaseManual implements MethodInterface
{
}