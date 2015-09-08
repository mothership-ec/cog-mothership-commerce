<?php

namespace Message\Mothership\Commerce\Order;

/**
 * Class OrderOrder
 * @package Message\Mothership\Commerce\Order
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Class of constants for determining the order in which orders will be loaded from the database.
 * Apologies for the class name but the English language has too many meanings for the word 'order'
 */
class OrderOrder
{
	const CREATED_DATE         = "commerce.order.order.date.created";
	const CREATED_DATE_REVERSE = "commerce.order.order.date.created.reverse";
	const UPDATED_DATE         = "commerce.order.order.date.updated";
	const UPDATED_DATE_REVERSE = "commerce.order.order.date.updated.reverse";
	const ID                   = "commerce.order.order.id";
	const ID_REVERSE           = "commerce.order.order.id.reverse";
	const NONE                 = "commerce.order.order.none";
}