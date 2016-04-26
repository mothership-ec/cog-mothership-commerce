<?php

namespace Message\Mothership\Commerce\Report;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\Routing\UrlGenerator;
use Message\Cog\Event\DispatcherInterface;

use Message\Mothership\Report\Report\AbstractReport;
use Message\Mothership\Report\Event\ReportEvent;
use Message\Mothership\Report\Chart\TableChart;
use Message\Mothership\Report\Filter\DateRange;
use Message\Mothership\Report\Filter\Choices;
use Message\Mothership\Report\Filter\Collection as FilterCollecion;

use Message\Mothership\Commerce\Report\Filter\BrandFilter;
use Message\Mothership\Commerce\Events;

abstract class AbstractSales extends AbstractReport
{
	private $_eventDispatcher;

	/**
	 * Constructor.
	 *
	 * @param QueryBuilderFactory   $builderFactory
	 * @param UrlGenerator          $routingGenerator
	 * @param DispatcherInterface   $eventDispatcher
	 * @param array                 $currencies
	 */
	public function __construct(
		QueryBuilderFactory $builderFactory,
		UrlGenerator $routingGenerator,
		DispatcherInterface $eventDispatcher,
		array $currencies
	)
	{
		parent::__construct($builderFactory, $routingGenerator, $eventDispatcher);
		$this->_eventDispatcher = $eventDispatcher;
		$this->_charts[]   = new TableChart;
		$this->_filters->add(new DateRange);
		// Params for Choices filter: unique filter name, label, choices, multi-choice
		$this->_filters->add(new Choices(
			"currency",
			"Currency",
			$currencies,
			false
		));
		$this->_filters->add(new Choices(
			"type",
			"Sale Type",
			[
				'Order',
				'Return',
				'Exchange',
				'Shipping',
			],
			true
		));
		$this->_filters->add(new Choices(
			"source",
			"Source",
			[
				'Web',
				'EPOS',
			],
			true
		));
	}
	/**
	 * Retrieves JSON representation of the data and columns.
	 * Applies data to chart types set on report.
	 *
	 * @return Array  Returns all types of chart set on report with appropriate data.
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
	 * Get the filtered bas query
	 * @return \Message\Cog\DB\QueryBuilder The base QueryBuilder
	 */
	protected function _getFilteredQuery()
	{
		$unions = $this->_dispatchEvent($this->getFilters())->getQueryBuilders();

		$fromQuery = $this->_builderFactory->getQueryBuilder();

		foreach($unions as $query) {
			$fromQuery->unionAll($query);
		}

		return $fromQuery;
	}

	/**
	 * Dispatch event.
	 *
	 * @param  FilterCollecion $filters  Any filters to be used in subqueries.
	 *
	 * @return ReportEvent
	 */
	protected function _dispatchEvent(FilterCollecion $filters = null)
	{
		$event = new ReportEvent;

		if ($filters) {
			$event->setFilters($filters);
		}

		return $this->_eventDispatcher->dispatch(Events::SALES_REPORT, $event);
	}

}