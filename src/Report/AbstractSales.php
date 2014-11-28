<?php

namespace Message\Mothership\Commerce\Report;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\Localisation\Translator;
use Message\Cog\Routing\UrlGenerator;
use Message\Cog\Event\DispatcherInterface;

use Message\Mothership\Report\Report\AbstractReport;
use Message\Mothership\Report\Event\ReportEvent;

use Message\Mothership\Commerce\Events;
use Message\Mothership\Report\Filter\Collection as FilterCollecion;

abstract class AbstractSales extends AbstractReport
{
	private $_eventDispatcher;

	protected $_charts = [];

	public function __construct(QueryBuilderFactory $builderFactory, Translator $trans, UrlGenerator $routingGenerator, DispatcherInterface $eventDispatcher)
	{
		$this->_eventDispatcher = $eventDispatcher;
		parent::__construct($builderFactory, $trans, $routingGenerator);
	}

	protected function _dispatchEvent(FilterCollecion $filters = null)
	{
		$event = new ReportEvent;

		if ($filters) {
			$event->setFilters($filters);
		}
		
		return $this->_eventDispatcher->dispatch(Events::SALES_REPORT, $event);
	}

}