<?php

namespace Message\Mothership\Commerce\Report;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\Localisation\Translator;
use Message\Cog\Routing\UrlGenerator;

use Message\Mothership\Report\Report\AbstractReport;
use Message\Mothership\Report\Chart\TableChart;

class SalesByProduct extends AbstractReport
{
	public function __construct(QueryBuilderFactory $builderFactory, Translator $trans, UrlGenerator $routingGenerator)
	{
		$this->name = 'sales_by_product';
		$this->displayName = 'Sales by Product';
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
			['type' => 'string',	'name' => "Product",	],
			['type' => 'string',	'name' => "Option",		],
			['type' => 'string',	'name' => "Currency",	],
			['type' => 'number',	'name' => "Net",		],
			['type' => 'number',	'name' => "Tax",		],
			['type' => 'number',	'name' => "Gross",		],
			['type' => 'number',	'name' => "Number Sold",],
		];

		return json_encode($columns);
	}

	private function _getQuery()
	{
		$unions = [];

		$sales = $this->_builderFactory->getQueryBuilder();
		$unions[] = $sales
			->select('item.created_at AS "Date"')
			->select('order_summary.currency_id AS "Currency"')
			->select('IFNULL(item.net, 0) AS "Net"')
			->select('IFNULL(item.tax, 0) AS "Tax"')
			->select('IFNULL(item.gross, 0) AS "Gross"')
			->select('order_summary.type AS "Source"')
			->select('"Order" AS "Type"')
			->select('item.item_id AS "Item_ID"')
			->select('item.order_id AS "Order_ID"')
			->select('"" AS "Return_ID"')
			->select('item.product_id AS "Product_ID"')
			->select('item.product_name AS "Product"')
			->select('item.options AS "Option"')
			->select('country AS "Country"')
			->select('CONCAT(user.forename," ",user.surname) AS "User"')
			->select('user.email AS "Email"')
			->select('order_summary.user_id AS "User_id"')
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

		$returns = $this->_builderFactory->getQueryBuilder();
		$unions[] = $returns
			->select('item.completed_at AS "Date"')
			->select('return.currency_id AS "Currency"')
			->select('IFNULL(-item.net, 0) AS "Net"')
			->select('IFNULL(-item.tax, 0) AS "Tax"')
			->select('IFNULL(-item.gross, 0) AS "Gross"')
			->select('return.type AS "Source"')
			->select('"Return" AS "Type"')
			->select('item.item_id AS "Item_ID"')
			->select('item.order_id AS "Order_ID"')
			->select('item.return_id AS "Return_ID"')
			->select('item.product_id AS "Product_ID"')
			->select('item.product_name AS "Product"')
			->select('item.options AS "Option"')
			->select('country AS "Country"')
			->select('CONCAT(user.forename," ",user.surname) AS "User"')
			->select('user.email AS "Email"')
			->select('user.user_id AS "User_id"')
			->from('return_item AS item')
			->join('`return`', 'item.return_id = return.return_id')
			->leftJoin('order_address', 'item.order_id = order_address.order_id AND order_address.type = "delivery"')
			->leftJoin('user', 'return.created_by = user.user_id')
			->where('item.status_code >= 2200')
			->where('item.product_id NOT IN (9)')
			->where('item.completed_at BETWEEN UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 12 MONTH)) AND UNIX_TIMESTAMP(NOW())')
		;

		$exchanges = $this->_builderFactory->getQueryBuilder();
		$unions[] = $exchanges
			->select('return_item.completed_at AS "Date"')
			->select('order_summary.currency_id AS "Currency"')
			->select('IFNULL(item.net, 0) AS "Net"')
			->select('IFNULL(item.tax, 0) AS "Tax"')
			->select('IFNULL(item.gross, 0) AS "Gross"')
			->select('order_summary.type AS "Source"')
			->select('"Exchange" AS "Type"')
			->select('item.item_id AS "Item_ID"')
			->select('item.order_id AS "Order_ID"')
			->select('return_item.return_id AS "Return_ID"')
			->select('item.product_id AS "Product_ID"')
			->select('item.product_name AS "Product"')
			->select('item.options AS "Option"')
			->select('country AS "Country"')
			->select('CONCAT(user.forename," ",user.surname) AS "User"')
			->select('user.email AS "Email"')
			->select('order_summary.user_id AS "User_id"')
			->from('order_item AS item')
			->join('order_summary', 'item.order_id = order_summary.order_id')
			->join('return_item', 'return_item.exchange_item_id = item.item_id')
			->leftJoin('order_address', 'order_summary.order_id = order_address.order_id AND order_address.type = "delivery"')
			->leftJoin('user', 'order_summary.user_id = user.user_id')
			->where('return_item.status_code >= 2200')
			->where('item.product_id NOT IN (9)')
			->where('item.created_at BETWEEN UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 12 MONTH)) AND UNIX_TIMESTAMP(NOW())')
		;

		$salesShipping = $this->_builderFactory->getQueryBuilder();
		$unions[] = $salesShipping
			->select('order_summary.created_at AS "Date"')
			->select('order_summary.currency_id AS "Currency"')
			->select('IFNULL(net, 0) AS "Net"')
			->select('IFNULL(tax, 0) AS "Tax"')
			->select('IFNULL(gross, 0) AS "Gross"')
			->select('order_summary.type AS "Source"')
			->select('"Shipping" AS "Type"')
			->select('"" AS "Item_ID"')
			->select('order_summary.order_id AS "Order_ID"')
			->select('"" AS "Return_ID"')
			->select('"" AS "Product_ID"')
			->select('display_name AS "Product"')
			->select('"" AS "Option"')
			->select('country AS "Country"')
			->select('CONCAT(user.forename," ",user.surname) AS "User"')
			->select('user.email AS "Email"')
			->select('order_summary.user_id AS "User_id"')
			->from('order_shipping')
			->join('order_summary', 'order_shipping.order_id = order_summary.order_id')
			->leftJoin('order_address', 'order_summary.order_id = order_address.order_id AND order_address.type = "delivery"')
			->leftJoin('user', 'order_summary.user_id = user.user_id')
			->where('order_summary.status_code >= 0')
			->where('order_summary.created_at BETWEEN UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 12 MONTH)) AND UNIX_TIMESTAMP(NOW())')
		;

		$fromQuery = $this->_builderFactory->getQueryBuilder();
		foreach($unions as $query) {
			$fromQuery->unionAll($query);
		}

		$queryBuilder = $this->_builderFactory->getQueryBuilder();
		$queryBuilder
			->select('totals.product_id AS "ID"')
			->select('totals.product AS "Product"')
			->select('totals.option AS "Option"')
			->select('totals.currency AS "Currency"')
			->select('SUM(totals.net) AS "Net"')
			->select('SUM(totals.tax) AS "Tax"')
			->select('SUM(totals.gross) AS "Gross"')
			->select('COUNT(totals.gross) AS "NumberSold"')
			->from('totals', $fromQuery)
			->orderBy('gross DESC')
			->groupBy('product_id, currency')
		;

		return $queryBuilder->getQuery();
	}

	private function _dataTransform($data)
	{
		$result = [];

		foreach ($data as $row) {
			$result[] = [
				[ 'v' => ucwords($row->Product), 'f' => (string) '<a href ="'.$this->generateUrl('ms.commerce.product.edit.attributes', ['productID' => $row->ID]).'">'.ucwords($row->Product).'</a>'],
				ucwords($row->Option),
				$row->Currency,
				[ 'v' => (float) $row->Net, 'f' => (string) number_format($row->Net,2,'.',',')],
				[ 'v' => (float) $row->Tax, 'f' => (string) number_format($row->Tax,2,'.',',')],
				[ 'v' => (float) $row->Gross, 'f' => (string) number_format($row->Gross,2,'.',',')],
				$row->NumberSold,
			];
		}

		return json_encode($result);
	}
}