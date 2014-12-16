<?php

namespace Message\Mothership\Commerce\Report;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\Routing\UrlGenerator;
use Message\Cog\ValueObject\DateTimeImmutable;

use Message\Mothership\Report\Report\AbstractReport;
use Message\Mothership\Report\Chart\TableChart;
use Message\Mothership\Report\Filter\DateForm;

class StockSummary extends AbstractReport
{
	/**
	 * Constructor.
	 *
	 * @param QueryBuilderFactory   $builderFactory
	 * @param UrlGenerator          $routingGenerator
	 */
	public function __construct(QueryBuilderFactory $builderFactory, UrlGenerator $routingGenerator)
	{
		parent::__construct($builderFactory, $routingGenerator);
		$this->name = 'stock_summary';
		$this->displayName = 'Stock Summary';
		$this->reportGroup = "Products";
		$this->description =
			"This report displays the stock levels per unit.
			By default it displays the current stock. Snapshots of stock are made at the end of each day and
			can be selected from the date filter.";
		$this->_charts  = [new TableChart];
		$this->_filters->add(new DateForm);
	}

	/**
	 * Retrieves JSON representation of the data and columns.
	 * Applies data to chart types set on report.
	 *
	 * @return Array  Returns all types of chart set on report with appropriate data.
	 */
	public function getCharts()
	{
		$data = $this->_dataTransform($this->_getQuery()->run(),"json");
		$columns = $this->getColumns();

		foreach ($this->_charts as $chart) {
			$chart->setColumns($columns);
			$chart->setData($data);
		}

		return $this->_charts;
	}

	/**
	 * Set columns for use in reports.
	 *
	 * @return String  Returns columns in JSON format.
	 */
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

	/**
	 * Gets stock levels for all units.
	 * Order by CATEGORY, PRODUCT, OPTIONS.
	 *
	 * @return Query
	 */
	protected function _getQuery()
	{
		$queryBuilder = $this->_builderFactory->getQueryBuilder();

		$queryBuilder->from("product_unit_stock stock");

		// Filter dates
		if($this->_filters->get('date_form')->getDateChoice()) {

			$dateFilter = $this->_filters->get('date_form');
			$today = new DateTimeImmutable();
			$date = $dateFilter->getDateChoice();

			if($date->format('YMd') < $today->format('YMd')) {

				$queryBuilder->from("product_unit_stock_snapshot stock");
				$queryBuilder->where('FROM_UNIXTIME(stock.created_at) <= DATE_ADD(FROM_UNIXTIME(?d), INTERVAL 3 HOUR)', [$date->format('U')]);
				$queryBuilder->where('?d <= stock.created_at', [$date->format('U')]);
			}
		}

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

	/**
	 * Takes the data and transforms it into a useable format.
	 *
	 * @param  $data    DB\Result  The data from the report query.
	 * @param  $output  String     The type of output required.
	 *
	 * @return String|Array  Returns columns as string in JSON format or array.
	 */
	protected function _dataTransform($data, $output = null)
	{
		$result = [];

		if ($output === "json") {
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

		} else {

			foreach ($data as $row) {
				$result[] = [
					$row->Name,
					$row->ID,
					$row->Options,
					$row->Stock,
				];
			}
			return $result;
		}
	}
}

