<?php

namespace Message\Mothership\Commerce\Report;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\Localisation\Translator;
use Message\Cog\Routing\UrlGenerator;

use Message\Mothership\Report\Report\AbstractReport;
use Message\Mothership\Report\Chart\TableChart;

class PaymentsAndRefunds extends AbstractReport
{
	public function __construct(QueryBuilderFactory $builderFactory, Translator $trans, UrlGenerator $routingGenerator)
	{
		$this->name = 'payments_refunds';
		$this->displayName = 'Payments & Refunds';
		$this->reportGroup = 'Transactions';
		$this->_charts = [new TableChart];
		parent::__construct($builderFactory,$trans,$routingGenerator);
	}

	public function getCharts()
	{
		$data = $this->_dataTransform($this->_getQuery()->run());
		$columns = $this->getColumns();

		foreach ($this->_charts as $chart) {
			$chart->setColumns($columns);
			$chart->setData($data);
		}

		return $this->_charts;
	}

	public function getColumns()
	{
		$columns = [
			['type' => 'string',	'name' => "Date",		 ],
			['type' => 'string',	'name' => "Created By",	 ],
			['type' => 'string',	'name' => "Currency",	 ],
			['type' => 'string',	'name' => "Method",		 ],
			['type' => 'number',	'name' => "Amount",		 ],
			['type' => 'string',	'name' => "Type",		 ],
			['type' => 'string',	'name' => "Order/Return",],
		];

		return json_encode($columns);
	}

	private function _getQuery()
	{
		$unions = [];

		$payments = $this->_builderFactory->getQueryBuilder();
		$unions[] = $payments
			->select('payment.payment_id AS ID')
			->select('payment.created_at')
			->select('payment.created_by AS created_by_id')
			->select('CONCAT(user.forename," ",user.surname) AS created_by')
			->select('currency_id as currency')
			->select('method')
			->select('amount')
			->select('"Payment" AS type')
			->select('order_id AS order_return_id')
			->select('reference')
			->from('payment')
			->leftJoin('order_payment','payment.payment_id = order_payment.payment_id')
			->join('user','user.user_id = payment.created_by')
		;

		$refunds = $this->_builderFactory->getQueryBuilder();
		$unions[] = $refunds
			->select('refund.refund_id AS ID')
			->select('refund.created_at')
			->select('refund.created_by AS created_by_id')
			->select('CONCAT(user.forename," ",user.surname) AS created_by')
			->select('currency_id as currency')
			->select('method')
			->select('-amount')
			->select('"Refund" AS type')
			->select('return_id AS order_return_id')
			->select('reference')
			->from('refund')
			->leftJoin('return_refund','refund.refund_id = return_refund.refund_id')
			->join('user','user.user_id = refund.created_by')
		;

		$fromQuery = $this->_builderFactory->getQueryBuilder();
		foreach($unions as $query) {
			$fromQuery->unionAll($query);
		}

		$queryBuilder = $this->_builderFactory->getQueryBuilder();
		$queryBuilder
			->select('*')
			->from('t1',$fromQuery)
			->orderBy('created_at DESC')
			->limit('25')
		;

		return $queryBuilder->getQuery();
	}

	private function _dataTransform($data)
	{
		$result = [];

		foreach ($data as $row) {
			if ($row->type == "Payment") {
				$url = $this->generateUrl('ms.commerce.order.detail.view', ['orderID' => (int) $row->order_return_id]);
			} else {
				$url = $this->generateUrl('ms.commerce.return.view', ['returnID' => (int) $row->order_return_id]);
			}

			$result[] = [
				date('Y-m-d H:i', $row->created_at),
				'<a href ="'.$this->generateUrl('ms.cp.user.admin.detail.edit', ['userID' => (int) $row->created_by_id]).'">'.$row->created_by.'</a>',
				$row->currency,
				$row->method,
				[ 'v' => (float) $row->amount, 'f' => (string) number_format($row->amount,2,'.',',')],
				$row->type,
				'<a href ="'.$url.'">'.$row->order_return_id.'</a>',
			];
		}

		return json_encode($result);
	}
}