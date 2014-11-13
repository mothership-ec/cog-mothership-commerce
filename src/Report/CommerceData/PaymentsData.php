<?php

namespace Message\Mothership\Commerce\Report\CommerceData;

use Message\Cog\DB\QueryBuilderFactory;

class PaymentsData
{
	private $_builderFactory;

	public function __construct(QueryBuilderFactory $builderFactory)
	{
		$this->_builderFactory = $builderFactory;
	}

	public function getQueryBuilder()
	{
		$data = $this->_builderFactory->getQueryBuilder();

		$data
			->select('payment.payment_id AS ID')
			->select('payment.created_at')
			->select('payment.created_by AS user_id')
			->select('CONCAT(user.surname, ", ", user.forename) AS user')
			->select('currency_id as currency')
			->select('method')
			->select('amount')
			->select('"Payment" AS type')
			->select('order_id AS order_return_id')
			->select('reference')
			->from('payment')
			->leftJoin('order_payment','payment.payment_id = order_payment.payment_id')
			->join('user','user.user_id = payment.created_by')		;

		return $data;
	}
}
