<?php

namespace Message\Mothership\Commerce\Report;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\Localisation\Translator;
use Message\Cog\Routing\UrlGenerator;

use Message\Mothership\Report\Report\AbstractReport;
use Message\Mothership\Report\Chart\TableChart;

class SalesByLocation extends AbstractReport
{
	public function __construct(QueryBuilderFactory $builderFactory, Translator $trans, UrlGenerator $routingGenerator)
	{
		$this->name = 'sales_by_location';
		$this->displayName = 'Sales by Location';
		$this->reportGroup = "Sales";
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
			['type' => 'string', 	'name' => "Country",		],
			['type' => 'string',	'name' => "Currency",	],
			['type' => 'number',	'name' => "Net",		],
			['type' => 'number',	'name' => "Tax",		],
			['type' => 'number',	'name' => "Gross",		],
		];

		return json_encode($columns);
	}

	private function _getQuery()
	{
		$queryBuilder = $this->_builderFactory->getQueryBuilder();
		$salesQuery   = $this->_builderFactory->getQueryBuilder();

		$salesQuery
			->select('item.created_at AS date')
			->select('order_summary.currency_id AS currency')
			->select('IFNULL(item.net, 0) AS net')
			->select('IFNULL(item.tax, 0) AS tax')
			->select('IFNULL(item.gross, 0) AS gross')
			->select('order_summary.type AS type')
			->select('item.item_id AS item_id')
			->select('item.order_id AS order_id')
			->select('item.product_id AS product_id')
			->select('item.product_name AS product')
			->select('item.options AS `option`')
			->select('country AS `country`')
			->select('CONCAT(user.forename," ",user.surname) AS `user`')
			->select('user.email AS `email`')
			->select('order_summary.user_id AS `user_id`')
			->from('order_item AS item')
			->join('order_summary', 'item.order_id = order_summary.order_id')
			->leftJoin('order_address', 'order_summary.order_id = order_address.order_id AND order_address.type = "delivery"')
			->leftJoin('return_item', 'return_item.exchange_item_id = item.item_id')
			->leftJoin('user', 'order_summary.user_id = user.user_id')
			->where('order_summary.status_code >= 0')
			->where('item.product_id NOT IN (9)')
			->where('return_item.exchange_item_id IS NULL')
			->where('item.created_at BETWEEN UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 12 MONTH)) AND UNIX_TIMESTAMP(NOW())')
		;

		$queryBuilder
			->select('totals.country AS "Country"')
			->select('totals.currency AS "Currency"')
			->select('SUM(totals.net) AS "Net"')
			->select('SUM(totals.tax) AS "Tax"')
			->select('SUM(totals.gross) AS "Gross"')
			->from('totals', $salesQuery)
			->where('country IS NOT NULL')
			->groupBy('country, currency')
			->orderBy('SUM(totals.gross) DESC')
		;

		return $queryBuilder->getQuery();
	}

	private function _dataTransform($data)
	{
		$result = [];

		foreach ($data as $row) {
			$result[] = [
				$row->Country,
				$row->Currency,
				[ 'v' => (float) $row->Net, 'f' => (string) number_format($row->Net,2,'.',',')],
				[ 'v' => (float) $row->Tax, 'f' => (string) number_format($row->Tax,2,'.',',')],
				[ 'v' => (float) $row->Gross, 'f' => (string) number_format($row->Gross,2,'.',',')],
			];
		}

		return json_encode($result);
	}
}