<?php

namespace Message\Mothership\Commerce\Report;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Report\ReportInterface;
use Message\Mothership\Report\Report\AbstractReport;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Mothership\Report\Chart\TableChart;
use Message\Mothership\Report\Filter\DateFilter;


class StockSummary extends AbstractReport
{
	private $_to = [];
	private $_from = [];
	private $_builderFactory;
	private $_charts;
	private $_filters;

	public function __construct(QueryBuilderFactory $builderFactory)
	{
		$this->name = "stock-summary-report";
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
		$queryBuilder = $this->_builderFactory->getQueryBuilder();

			
		// if ($date){
			$queryBuilder->from("product_unit_stock stock");
		// } else {
		// 	$this->_queryBuilder->from("product_unit_stock_snapshot stock");
			// $this->_queryBuilder->where('FROM_UNIXTIME(stock.created_at) <= DATE_ADD(FROM_UNIXTIME(?), INTERVAL 3 HOUR)');
			// $this->_queryBuilder->where('? <= stock.created_at');
		// }

		$queryBuilder
			->select("product.category")
			->select("product.name")
			->select("options")
			->select("stock.stock")
			->join("unit","unit.unit_id = stock.unit_id","product_unit")
			->leftJoin("product","unit.product_id = product.product_id")
			->leftJoin("unit_options","unit_options.unit_id = unit.unit_id",
				$this->_builderFactory->getQueryBuilder()
					->select('unit_id')
					->select('revision_id')
					->select("GROUP_CONCAT(option_name, \": \", option_value SEPARATOR ', ') AS `options`")
					->from('t1',
						$this->_builderFactory->getQueryBuilder()
							->select('unit_id')
							->select('MAX(revision_id) AS revision_id')
							->select('option_name')
							->select('option_value')
							->from('product_unit_option')
							->groupBy('unit_id')
							->groupBy('option_name')
						)
					->groupBy('unit_id')
					->orderBy('option_value')
				)
			->where("stock.location = 'web'")
			->where("product.deleted_at IS NULL")
			->where("unit.deleted_at IS NULL")
			->where("unit.deleted_at IS NULL")
			->groupBy('stock.unit_id')
			->orderBy('product.category')
			->orderBy('product.name')
			->orderBy('options ASC')
		;

		return $queryBuilder->getQuery();

	}

	protected function dataTransform($data)
	{
		return $data->transpose();
	}
}

