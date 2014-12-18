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
	 */
	public function __construct(QueryBuilderFactory $builderFactory, UrlGenerator $routingGenerator, DispatcherInterface $eventDispatcher)
	{
		parent::__construct($builderFactory, $routingGenerator, $eventDispatcher);
		$this->_eventDispatcher = $eventDispatcher;
		$this->_charts[]   = new TableChart;
		$this->_filters->add(new DateRange);
		// Params for Choices filter: unique filter name, label, choices, multi-choice
		$this->_filters->add(new Choices(
			"currency",
			"Currency",
			[
				'GBP' => 'GBP',
				'JPY' => 'JPY',
			],
			false
		));
		$this->_filters->add(new Choices(
			"type",
			"Sale Type",
			[
				'Order' => 'Order',
				'Return' => 'Return',
				'Exchange' => 'Exchange',
				'shipping' => 'Shipping',
			],
			true
		));
		$this->_filters->add(new Choices(
			"source",
			"Source",
			[
				'web' => 'Web',
				'epos' => 'EPOS',
			],
			true
		));
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