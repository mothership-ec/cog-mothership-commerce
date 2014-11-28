<?php

namespace Message\Mothership\Commerce\Report;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\Localisation\Translator;
use Message\Cog\Routing\UrlGenerator;

use Message\Mothership\Report\Report\AbstractReport;
use Message\Mothership\Report\Chart\TableChart;
use Message\Mothership\Report\Filter\DateRangeFilter;

class StockSummary extends AbstractReport
{
	public function __construct(QueryBuilderFactory $builderFactory, Translator $trans, UrlGenerator $routingGenerator)
	{
		parent::__construct($builderFactory,$trans,$routingGenerator);
		$this->name = 'stock_summary';
		$this->displayName = 'Stock Summary';
		$this->reportGroup = "Products";
		$this->_charts  = [new TableChart];
		// $this->_filters->add(new DateRangeFilter);
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
			['type' => 'string', 'name' => "Category", ],
			['type' => 'string', 'name' => "Name",     ],
			['type' => 'string', 'name' => "Options",  ],
			['type' => 'number', 'name' => "Stock",    ],
		];

		return json_encode($columns);
	}

	private function _getQuery()
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
			->select('product.product_id AS "ID"')
			->select('product.category AS "Category"')
			->select('product.name AS "Name"')
			->select('options AS "Options"')
			->select('stock.stock AS "Stock"')
			->join("unit","unit.unit_id = stock.unit_id","product_unit")
			->leftJoin("product","unit.product_id = product.product_id")
			->leftJoin("unit_options","unit_options.unit_id = unit.unit_id",
				$this->_builderFactory->getQueryBuilder()
					->select('unit_id')
					->select('revision_id')
					->select('GROUP_CONCAT(option_value ORDER BY option_name SEPARATOR ", ") AS options')
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

	private function _dataTransform($data)
	{
		$result = [];

		foreach ($data as $row) {
			$result[] = [
				$row->Category,
				[
					'v' => ucwords($row->Name),
					'f' => (string) '<a href ="'.$this->generateUrl('ms.commerce.product.edit.attributes', ['productID' => $row->ID]).'">'
					.ucwords($row->Name).'</a>'
				],
				$row->Options,
				$row->Stock,
			];
		}

		return json_encode($result);
	}
}

