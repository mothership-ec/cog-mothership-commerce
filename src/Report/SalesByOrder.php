<?php

namespace Message\Mothership\Commerce\Report;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\Routing\UrlGenerator;
use Message\Cog\Event\DispatcherInterface;

use Message\Mothership\Report\Chart\TableChart;
use Message\Mothership\Report\Filter\DateRange;
use Message\Mothership\Report\Filter\Choices;


class SalesByOrder extends AbstractSales
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
		$this->name = 'sales_by_order';
		$this->displayName = 'Sales by Order';
		$this->description =
			"This report displays all completed orders.
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
			['type' => 'string', 'name' => "Order",    ],
			['type' => 'number', 'name' => "Date",     ],
			['type' => 'string', 'name' => "User",     ],
			['type' => 'string', 'name' => "Source",   ],
			['type' => 'string', 'name' => "Currency", ],
			['type' => 'number', 'name' => "Net",      ],
			['type' => 'number', 'name' => "Tax",      ],
			['type' => 'number', 'name' => "Gross",    ],
			['type' => 'string', 'name' => "Country",  ],
		];

		return json_encode($columns);
	}

	/**
	 * Dispatches event to get all sales, returns & shipping queries.
	 *
	 * Unions all sub queries & creates parent query.
	 * Sum all totals and grouping by ORDER.
	 * Order by DATE.
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
			->select('date AS "UnixDate"')
			->select('FROM_UNIXTIME(date, "%d-%b-%Y %k:%i") AS "Date"')
			->select('totals.order_id AS "Order"')
			->select('totals.user_id AS "UserID"')
			->select('CONCAT(totals.user_surname,", ",totals.user_forename) AS "User"')
			->select('totals.source AS "Source"')
			->select('totals.currency AS "Currency"')
			->select('SUM(totals.net) AS "Net"')
			->select('SUM(totals.tax) AS "Tax"')
			->select('SUM(totals.gross) AS "Gross"')
			->select('totals.country AS "Country"')
			->from('totals', $fromQuery)
			->orderBy('order_id DESC')
			->groupBy('order_id')
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
				'<a href ="'.$this->generateUrl('ms.commerce.order.detail.view', ['orderID' => (int) $row->Order]).'">'.$row->Order.'</a>',
				[ 'v' => (float) $row->UnixDate, 'f' => (string) $row->Date],
				$row->User ?
					[
						'v' => utf8_encode($row->User),
						'f' => (string) '<a href ="'.$this->generateUrl('ms.cp.user.admin.detail.edit', ['userID' => $row->UserID]).'">'
						.ucwords(utf8_encode($row->User)).'</a>'
					]
					: $row->User,
				ucwords($row->Source),
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
				$row->Country,
			];
		}

		return json_encode($result);
	}
}