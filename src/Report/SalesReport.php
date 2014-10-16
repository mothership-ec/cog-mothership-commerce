<?php

namespace Message\Mothership\Commerce\Report;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Report\ReportInterface;
use Message\Mothership\Report\Report\AbstractReport;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Mothership\Report\Chart\TableChart;
use Message\Mothership\Report\Filter\DateFilter;

class SalesReport extends AbstractReport
{
	private $_to = [];
	private $_from = [];
	private $_builderFactory;
	private $_charts;
	private $_filters;

	public function __construct(QueryBuilderFactory $builderFactory)
	{
		$this->name = "sales-report";
		$this->_builderFactory = $builderFactory;
		$this->_charts = [new TableChart];
		$this->_filters = [new DateFilter];
	}

	public function getName()
	{
		return $this->name;
	}

	public function getCharts()
	{
		$data = $this->dataTransform($this->getQuery()->run());

		foreach ($this->_charts as $chart) {
			$chart->setData($data);
		}

		return $this->_charts;
	}

	private function getQuery()
	{
		$queryBuilder = $this->_builderFactory->getQueryBuilder();
		$salesQuery = $this->_builderFactory->getQueryBuilder();

		$salesQuery
			->select('item.created_at AS date')
			->select('IFNULL(item.net, 0) AS net')
			->select('IFNULL(item.tax, 0) AS tax')
			->select('IFNULL(item.gross, 0) AS gross')
			->select('order_summary.type AS type')
			->select('item.item_id AS item_id')
			->select('item.order_id AS order_id')
			->select('item.product_name AS product')
			->select('item.options AS `option`')
			->from('order_item AS item')
			->join('order_summary', 'item.order_id = order_summary.order_id')
			->leftJoin('return_item', 'return_item.exchange_item_id = item.item_id')
			->where('order_summary.status_code >= 0')
			->where('item.product_id NOT IN (9)')
			->where('return_item.exchange_item_id IS NULL')
			->where('item.created_at BETWEEN UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 12 MONTH)) AND UNIX_TIMESTAMP(NOW())')
		;

		$queryBuilder
			->select('DATE_FORMAT(from_unixtime(date),"%d %b %Y %h:%i") AS "Date"')
			->select('totals.order_id AS "Order"')
			->select('totals.item_id AS "Item"')
			->select('totals.type AS "Type"')
			->select('totals.product AS "Product"')
			->select('totals.option AS "Option"')
			->select('totals.net AS "Net"')
			->select('totals.tax AS "Tax"')
			->select('totals.gross AS "Gross"')
			->from('totals', $salesQuery)
			->orderBy('date DESC')
		;

		return $queryBuilder->getQuery();
	}

	protected function dataTransform($data)
	{
		$result = [];
		$result[] = $data->columns();

		foreach ($data as $row) {
			$result[] = get_object_vars($row);

		}

		return $result;
	}
}