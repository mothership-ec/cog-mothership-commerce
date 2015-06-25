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


class SalesByUnit extends AbstractSales
{
	public function __construct(
		QueryBuilderFactory $builderFactory,
		UrlGenerator $routingGenerator,
		DispatcherInterface $eventDispatcher,
		array $currencies
	)
	{
		parent::__construct($builderFactory, $routingGenerator, $eventDispatcher, $currencies);

		$this->_setName('sales_by_unit');
		$this->_setDisplayName('Sales by Unit');
		$this->_setReportGroup('Sales');
		$this->_setDescription('
			This report groups the total income by unit.
			By default it includes all data(orders, returns, shipping) from the last month (by completed date)
		');
		$startDate = new \DateTime;
		$this->getFilters()->get('date_range')->setStartDate($startDate->setTimestamp(strtotime(date('Y-m-d H:i')." -1 month")));
	}

	public function getColumns()
	{
		return [
			'Product'     => 'string',
			'Option'      => 'string',
			'Currency'    => 'string',
			'Net'         => 'number',
			'Tax'         => 'number',
			'Gross'       => 'number',
			'Number Sold' => 'number',
		];
	}

	protected function _dataTransform($data, $output = null)
	{}

	protected function _getQuery()
	{}
}