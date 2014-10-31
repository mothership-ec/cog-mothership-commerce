<?php

namespace Message\Mothership\Commerce\Report;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\Localisation\Translator;
use Message\Cog\Routing\UrlGenerator;

use Message\Mothership\Report\Report\AbstractReport;
use Message\Mothership\Report\Chart\TableChart;

class SalesByItem extends AbstractReport
{
	public function __construct(QueryBuilderFactory $builderFactory, Translator $trans, UrlGenerator $routingGenerator)
	{
		$this->name = 'sales_by_item';
		$this->displayName = 'Sales by Item';
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
			['type' => 'number', 	'name' => "Date",		],
			['type' => 'string',	'name' => "Order",		],
			['type' => 'string',	'name' => "Type",		],
			['type' => 'string',	'name' => "Product",	],
			['type' => 'string',	'name' => "Option",		],
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
		$salesQuery = $this->_builderFactory->getQueryBuilder();

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
			->select('date AS "UnixDate"')
			->select('FROM_UNIXTIME(date, "%d-%b-%Y") AS "Date"')
			->select('totals.order_id AS "Order"')
			->select('totals.item_id AS "Item"')
			->select('totals.type AS "Type"')
			->select('totals.product_id AS "Product_ID"')
			->select('totals.product AS "Product"')
			->select('totals.option AS "Option"')
			->select('totals.currency AS "Currency"')
			->select('totals.net AS "Net"')
			->select('totals.tax AS "Tax"')
			->select('totals.gross AS "Gross"')
			->from('totals', $salesQuery)
			->orderBy('date DESC')
			->limit('50')
		;

		return $queryBuilder->getQuery();
	}

	private function _dataTransform($data)
	{
		$result = [];

		foreach ($data as $row) {
			$result[] = [
				[ 'v' => (float) $row->UnixDate, 'f' => (string) $row->Date],
				'<a href ="'.$this->generateUrl('ms.commerce.order.detail.view', ['orderID' => (int) $row->Order]).'">'.$row->Order.'</a>',
				ucwords($row->Type),
				'<a href ="'.$this->generateUrl('ms.commerce.product.edit.attributes', ['productID' => (int) $row->Product_ID]).'">'.$row->Product.'</a>',
				ucwords($row->Option),
				$row->Currency,
				[ 'v' => (float) $row->Net, 'f' => (string) number_format($row->Net,2,'.',',')],
				[ 'v' => (float) $row->Tax, 'f' => (string) number_format($row->Tax,2,'.',',')],
				[ 'v' => (float) $row->Gross, 'f' => (string) number_format($row->Gross,2,'.',',')],
			];
		}

		return json_encode($result);
	}
}