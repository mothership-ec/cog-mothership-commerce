<?php

namespace Message\Mothership\Commerce\Product\Stock\Movement\Reason;

/**
 * All reasons available in commerce.
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class Reasons
{
	const NEW_ORDER       = 'new_order';
	const CANCELLED_ORDER = 'cancelled_order';
	const CANCELLED_ITEM  = 'cancelled_item';
}