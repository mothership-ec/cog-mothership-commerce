<?php

namespace Message\Mothership\Commerce\Report;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\Localisation\Translator;
use Message\Cog\Routing\UrlGenerator;
use Message\Cog\Event\DispatcherInterface;

use Message\Mothership\Report\Report\AbstractReport;
use Message\Mothership\Report\Event\ReportEvent;
use Message\Mothership\Report\Chart\TableChart;
use Message\Mothership\Report\Filter\DateRange;
use Message\Mothership\Report\Filter\Choices;

use Message\Mothership\Commerce\Events;
use Message\Mothership\Report\Filter\Collection as FilterCollecion;

abstract class AbstractSales extends AbstractReport
{
	private $_eventDispatcher;

	protected $_charts = [];

	public function __construct(QueryBuilderFactory $builderFactory, Translator $trans, UrlGenerator $routingGenerator, DispatcherInterface $eventDispatcher)
	{
		parent::__construct($builderFactory, $trans, $routingGenerator);
		$this->_eventDispatcher = $eventDispatcher;
		$this->_charts[]   = new TableChart;
		$this->_filters->add(new DateRange);
		$this->_filters->add(new Choices("currency", "Currency",[
				'GBP' => 'GBP',
				'JPY' => 'JPY'
			],false)
		);
		$this->_filters->add(new Choices("type", "Sale Type",[
				'Order' => 'Sales',
				'Return' => 'Return',
				'Exchange' => 'Exchange',
				'shipping' => 'Shipping'
			],true)
		);
		$this->_filters->add(new Choices("source", "Source",[
				'web' => 'Web',
				'epos' => 'EPOS'
			],true)
		);
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