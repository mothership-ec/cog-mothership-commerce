<?php

namespace Message\Mothership\Commerce\Order\Entity\Payment\Method;

use Message\Mothership\Commerce\Order\Entity\Payment\MethodInterface;
use Message\Mothership\Commerce\Payment\Method\Cheque as BaseCheque;

/**
 * Cheque payment method.
 *
 * @see Message\Mothership\Commerce\Payment\Method\Cheque
 *
 * @deprecated Left here for BC. To be removed.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Cheque extends BaseCheque implements MethodInterface
{
}