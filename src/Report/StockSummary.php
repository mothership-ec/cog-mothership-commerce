<?php

namespace Message\Mothership\Commerce\Report;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Report\ReportInterface;


class StockSummary extends AbstractReport
{
	private $_to = [];
	private $_from = [];
	private $_builderFactory;
	private $_charts;
	private $_filters;

	public function __construct(QueryBuilderFactory $builderFactory)
	{
		$this->_builderFactory = $builderFactory;
		$this->_charts = [new TableChart];
		$this->_filters = [new DateFilter]
	}

	public function getCharts()
	{

		$data = $this->getQuery()->run();

		foreach ($this->_charts as $chart) {
			// populate $chart from getQuery->run();
			// $chart->setData($data);
		}

		return $this->_charts;
	}

	/**
	 * Gets the query as an string
	 *
	 * @param  string | null     $from        Date from which to get data
	 * @param  string | null     $to          Get data to this date
	 *
	 * @return string
	 */
	private function getQuery()
	{
		if ($date) { // get from filter
			$this->_date = $date;
		}

		$queryBuilder = $this->_builderFactory->getQueryBuilder();

		$queryBuilder
			->select("product.category")
			->select("product.name")
			->select("options")
			->select("stock.stock");

		if ($date){
			$this->_queryBuilder->from("product_unit_stock stock");
		} else {
			$this->_queryBuilder->from("product_unit_stock_snapshot stock");
		}

		$this->_queryBuilder
			->join("unit","unit.unit_id = stock.unit_id","product_unit")
			->leftJoin("product","unit.product_id = product.product_id")
			->leftJoin("unit_options","unit_options.unit_id = unit.unit_id",
					$this->_builderFactory->getQueryBuilder()
						->select()
				)
			->


		return $queryBuilder->getQuery();

	}
}

