<?php

namespace Message\Mothership\Commerce\Product;

class Events
{
	const STOCK_MOVEMENT          = 'commerce.product.stock.movement';
	const PRODUCT_UPLOAD_CREATE   = 'commerce.product_upload.product_create';
	const UNIT_UPLOAD_CREATE      = 'commerce.product_upload.unit_create';
	const PRODUCT_UPLOAD_COMPLETE = 'commerce.product_upload.complete';
}