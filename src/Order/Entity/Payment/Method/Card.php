<?php

namespace Message\Mothership\Commerce\Order\Entity\Payment\Method;

use Message\Mothership\Commerce\Order\Entity\Payment\MethodInterface;
use Message\Mothership\Commerce\Payment\Method\Card as BaseCard;

/**
 * Card payment method.
 *
 * @see Message\Mothership\Commerce\Payment\Method\Card
 *
 * @deprecated Left here for BC. To be removed.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Card extends BaseCard implements MethodInterface
{
}