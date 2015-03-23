<?php

namespace Message\Mothership\Commerce;

final class Events
{
	const PRODUCT_SELECTOR_BUILD   = 'commerce.product_selector.form.build';
	const PRODUCT_SELECTOR_PROCESS = 'commerce.product_selector.form.process';
	const CURRENCY_CHANGE          = 'commerce.currency.change';
	const SALES_REPORT             = 'commerce.report.sales-report';
	const TRANSACTIONS_REPORT      = 'commerce.report.transaction-report';
}