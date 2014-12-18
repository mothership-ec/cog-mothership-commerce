<?php

namespace Message\Mothership\Commerce\Report;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\Routing\UrlGenerator;
use Message\Cog\Event\DispatcherInterface;

use Message\Mothership\Report\Chart\TableChart;
use Message\Mothership\Report\Filter\DateRange;
use Message\Mothership\Report\Filter\Choices;


class SalesByDay extends AbstractSales
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
		$this->name        = 'sales_by_day';
		$this->displayName = 'Sales by Day';
		$this->description =
			"This report groups the total income by the day the order/return was completed.
			By default it includes all data (orders, returns, shipping) from the last month (by completed date).";
		$this->reportGroup = "Sales";
		$startDate = new \DateTime;
		$this->getFilters()->get('date_range')->setStartDate($startDate->setTimestamp(strtotime(date('Y-m-d H:i')." -1 month")));
	}

	/**
	 * Retrieves JSON representation of the data and columns.
	 * Applies data to chart types set on report.
	 *
	 * @return array  Returns all types of chart set on report with appropriate data.
	 */
	public function getCharts()
	{
		$data = $this->_dataTransform($this->_getQuery()->run(), "json");
		$columns = $this->_parseColumns($this->getColumns());

		foreach ($this->_charts as $chart) {
			$chart->setColumns($columns);
			$chart->setData($data);
		}

		return $this->_charts;
	}

	/**
	 * Set columns for use in reports.
	 *
	 * @return array  Returns array of columns as keys with format for Google Charts as the value.
	 */
	public function getColumns()
	{
		return [
			'Date'     => 'number',
			'Currency' => 'string',
			'Net'      => 'number',
			'Tax'      => 'number',
			'Gross'    => 'number',
		];
	}

	/**
	 * Dispatches event to get all sales, returns & shipping queries.
	 *
	 * Unions all sub queries & creates parent query.
	 * Sum all totals and grouping by DATE & CURRENCY.
	 * Order by DATE.
	 *
	 * @return Query
	 */
	protected function _getQuery()
	{
		$unions = $this->_dispatchEvent($this->getFilters())->getQueryBuilders();

		$fromQuery = $this->_builderFactory->getQueryBuilder();

		foreach($unions as $query) {
			$fromQuery->unionAll($query);
		}

		$queryBuilder = $this->_builderFactory->getQueryBuilder();
		$queryBuilder
			->select('date AS "UnixDate"')
			->select('FROM_UNIXTIME(date, "%d-%b-%Y") AS "Date"')
			->select('totals.currency AS "Currency"')
			->select('SUM(totals.net) AS "Net"')
			->select('SUM(totals.tax) AS "Tax"')
			->select('SUM(totals.gross) AS "Gross"')
			->from('totals', $fromQuery)
			->groupBy('FROM_UNIXTIME(date, "%d-%b-%Y"), currency')
			->orderBy('UnixDate DESC')
		;

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
	 * @param  $data    DB\Result    The data from the report query.
	 * @param  $output  string|null  The type of output required.
	 *
	 * @return string|array  Returns data as string in JSON format or array.
	 */
	protected function _dataTransform($data, $output = null)
	{
		$result = [];

		if ($output === "json") {

			foreach ($data as $row) {
				$result[] = [
					[
						'v' => (float) $row->UnixDate,
						'f' => (string) $row->Date
					],
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
				];
			}
			return json_encode($result);

		} else {

			foreach ($data as $row) {
				$result[] = [
					$row->Date,
					$row->Currency,
					$row->Net,
					$row->Tax,
					$row->Gross
				];
			}
			return $result;
		}
	}
}