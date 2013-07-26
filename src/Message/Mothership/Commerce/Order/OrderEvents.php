<?php

namespace Message\Mothership\Commerce\Order;

class OrderEvents
{
	const CREATE_START       = 'commerce.order.create.start';
	const CREATE_COMPLETE    = 'commerce.order.create.complete';
	const EDIT               = 'commerce.order.edit';
	const SET_STATUS         = 'commerce.order.status';
	const ITEM_STATUS_CHANGE = 'commerce.order.item.status.change';
}