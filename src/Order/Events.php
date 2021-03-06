<?php

namespace Message\Mothership\Commerce\Order;

final class Events
{
	const ASSEMBLER_UPDATE      = 'commerce.order.assembler.update';
	const CREATE_VALIDATE       = 'commerce.order.create.validate';
	const CREATE_START          = 'commerce.order.create.start';
	const CREATE_END            = 'commerce.order.create.end';
	const CREATE_COMPLETE       = 'commerce.order.create.complete';
	const EDIT                  = 'commerce.order.edit';
	const DELETE_START          = 'commerce.order.delete.start';
	const DELETE_END            = 'commerce.order.delete.end';
	const SET_STATUS            = 'commerce.order.status';
	const STATUS_CHANGE         = 'commerce.order.status.change';
	const ITEM_STATUS_CHANGE    = 'commerce.order.item.status.change';
	const DISPATCH_POSTAGE_AUTO = 'commerce.order.dispatch.postage.automatically';
	const DISPATCH_SHIPPED      = 'commerce.order.dispatch.shipped';
	const DISPATCH_NOTIFICATION = 'commerce.order.dispatch.notification';
	const ORDER_CANCEL_REFUND   = 'commerce.order.cancel.refund';
	const ITEM_CANCEL_REFUND    = 'commerce.order.item.cancel.refund';

	const ENTITY_CREATE         = 'commerce.order.entity.create';
	const ENTITY_CREATE_END     = 'commerce.order.entity.create.end';

	const BUILD_ORDER_SIDEBAR   = 'commerce.order.sidebar.create';
	const BUILD_ORDER_TABS      = 'commerce.order.tabs.create';

	const UPDATE_FAILED         = 'commerce.order.update_failed';
}