<?php

namespace Message\Mothership\Commerce\Report;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\Routing\UrlGenerator;
use Message\Cog\Event\DispatcherInterface;

use Message\Mothership\Report\Report\AbstractReport;
use Message\Mothership\Report\Event\ReportEvent;
use Message\Mothership\Report\Filter\Collection as FilterCollecion;

use Message\Mothership\Commerce\Events;

abstract class AbstractTransactions extends AbstractReport
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

		return $this->_eventDispatcher->dispatch(Events::TRANSACTIONS_REPORT, $event);
	}

}