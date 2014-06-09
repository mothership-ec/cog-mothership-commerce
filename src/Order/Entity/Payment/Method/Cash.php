<?php

namespace Message\Mothership\Commerce\Order\Entity\Payment\Method;

use Message\Mothership\Commerce\Order\Entity\Payment\MethodInterface;
use Message\Mothership\Commerce\Payment\Method\Cash as BaseCash;

/**
 * Cash payment method.
 *
 * @see Message\Mothership\Commerce\Payment\Method\Cash
 *
 * @deprecated Left here for BC. To be removed.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Cash extends BaseCash implements MethodInterface
{
}