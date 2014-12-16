<?php

namespace Message\Mothership\Commerce\Report;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\Routing\UrlGenerator;
use Message\Cog\Event\DispatcherInterface;

use Message\Mothership\Report\Chart\TableChart;
use Message\Mothership\Report\Filter\DateRange;
use Message\Mothership\Report\Filter\Choices;


class SalesByProduct extends AbstractSales
{
	/**
	 * Constructor.
	 *
	 * @param QueryBuilderFactory   $builderFactory
	 * @param UrlGenerator          $routingGenerator
	 * @param DispatcherInterface   $eventDispatcher
	 */
	public function __construct(QueryBuilderFactory $builderFactory, UrlGenerator $routingGenerator, DispatcherInterface $eventDispatcher)
	{
		parent::__construct($builderFactory, $routingGenerator, $eventDispatcher);
		$this->name = 'sales_by_product';
		$this->displayName = 'Sales by Product';
		$this->description =
			"This report groups the total income by product.
			By default it includes all data (orders, returns, shipping) from the last month (by completed date).";
		$this->reportGroup = "Sales";
	}

	/**
	 * Retrieves JSON representation of the data and columns.
	 * Applies data to chart types set on report.
	 *
	 * @return Array  Returns all types of chart set on report with appropriate data.
	 */
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

	/**
	 * Set columns for use in reports.
	 *
	 * @return String  Returns columns in JSON format.
	 */
	public function getColumns()
	{
		$columns = [
			['type' => 'string', 'name' => "Product",     ],
			['type' => 'string', 'name' => "Option",      ],
			['type' => 'string', 'name' => "Currency",    ],
			['type' => 'number', 'name' => "Net",         ],
			['type' => 'number', 'name' => "Tax",         ],
			['type' => 'number', 'name' => "Gross",       ],
			['type' => 'number', 'name' => "Number Sold", ],
		];

		return json_encode($columns);
	}

	/**
	 * Dispatches event to get all sales, returns & shipping queries.
	 *
	 * Unions all sub queries & creates parent query.
	 * Sum all totals and grouping by PRODUCT & CURRENCY.
	 * Order by GROSS DESC.
	 *
	 * @return Query
	 */
	protected function _getQuery()
	{
		$unions = $this->_dispatchEvent()->getQueryBuilders();

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
			->where('product_id IS NOT NULL')
			->orderBy('gross DESC')
			->groupBy('product_id, currency')
		;

		// Filter dates
		if($this->_filters->exists('date_range')) {

			$defaultDate = 'date BETWEEN UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 MONTH)) AND UNIX_TIMESTAMP(NOW())';

			$dateFilter = $this->_filters->get('date_range');

			if($date = $dateFilter->getStartDate()) {
				$queryBuilder->where('date > ?d', [$date->format('U')]);
				$defaultDate = NULL;
			}

			if($date = $dateFilter->getEndDate()) {
				$queryBuilder->where('date < ?d', [$date->format('U')]);
				$defaultDate = NULL;
			}

			if($defaultDate) {
				$queryBuilder->where($defaultDate);
			}
		}

		// Filter currency
		if($this->_filters->exists('currency')) {
			$currency = $this->_filters->get('currency');
			if($currency = $currency->getChoices()) {
				is_array($currency) ?
					$queryBuilder->where('Currency IN (?js)', [$currency]) :
					$queryBuilder->where('Currency = (?s)', [$currency])
				;
			}
		}

		// Filter source
		if($this->_filters->exists('source')) {
			$source = $this->_filters->get('source');
			if($source = $source->getChoices()) {
				is_array($source) ?
					$queryBuilder->where('Source IN (?js)', [$source]) :
					$queryBuilder->where('Source = (?s)', [$source])
				;
			}
		}

		// Filter type
		if($this->_filters->exists('type')) {
			$type = $this->_filters->get('type');
			if($type = $type->getChoices()) {
				is_array($type) ?
					$queryBuilder->where('Type IN (?js)', [$type]) :
					$queryBuilder->where('Type = (?s)', [$type])
				;
			}
		}

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

		foreach ($data as $row) {
			$result[] = [
				$row->ID ?
					[
						'v' => ucwords($row->Product),
						'f' => (string) '<a href ="'.$this->generateUrl('ms.commerce.product.edit.attributes', ['productID' => $row->ID]).'">'
						.ucwords($row->Product).'</a>'
					]
					: $row->Product,
				ucwords($row->Option),
				$row->Currency,
				[
					'v' => (float) $row->Net,
					'f' => (string) number_format($row->Net,2,'.',',')
				],
				[
					'v' => (float) $row->Tax,
					'f' => (string) number_format($row->Tax,2,'.',',')
				],
				[
					'v' => (float) $row->Gross,
					'f' => (string) number_format($row->Gross,2,'.',',')
				],
				$row->NumberSold,
			];
		}

		return json_encode($result);
	}
}