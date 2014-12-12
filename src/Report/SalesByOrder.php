<?php

namespace Message\Mothership\Commerce\Report;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\Localisation\Translator;
use Message\Cog\Routing\UrlGenerator;
use Message\Cog\Event\DispatcherInterface;

use Message\Mothership\Report\Chart\TableChart;
use Message\Mothership\Report\Filter\DateRange;
use Message\Mothership\Report\Filter\Choices;


class SalesByOrder extends AbstractSales
{
	public function __construct(QueryBuilderFactory $builderFactory, Translator $trans, UrlGenerator $routingGenerator, DispatcherInterface $eventDispatcher)
	{
		parent::__construct($builderFactory, $trans, $routingGenerator, $eventDispatcher);
		$this->name = 'sales_by_order';
		$this->displayName = 'Sales by Order';
		$this->reportGroup = "Sales";
	}

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

	public function getColumns()
	{
		$columns = [
			['type' => 'string', 'name' => "Order",    ],
			['type' => 'number', 'name' => "Date",     ],
			['type' => 'string', 'name' => "User",     ],
			['type' => 'string', 'name' => "Type",     ],
			['type' => 'string', 'name' => "Currency", ],
			['type' => 'number', 'name' => "Net",      ],
			['type' => 'number', 'name' => "Tax",      ],
			['type' => 'number', 'name' => "Gross",    ],
			['type' => 'string', 'name' => "Country",  ],
		];

		return json_encode($columns);
	}

	private function _getQuery()
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
			->select('totals.type AS "Type"')
			->select('totals.currency AS "Currency"')
			->select('SUM(totals.net) AS "Net"')
			->select('SUM(totals.tax) AS "Tax"')
			->select('SUM(totals.gross) AS "Gross"')
			->select('totals.country AS "Country"')
			->from('totals', $fromQuery)
			->orderBy('order_id DESC')
			->groupBy('order_id')
			->limit('300')
		;

		// filter dates
		if($this->_filters->count('date_range')) {

			$dateFilter = $this->_filters->get('date_range');

			if($date = $dateFilter->getStartDate()) {
				$queryBuilder->where('date > ?d', [$date->format('U')]);
			}

			if($date = $dateFilter->getEndDate()) {
				$queryBuilder->where('date < ?d', [$date->format('U')]);
			}

			if(!$dateFilter->getStartDate() && !$dateFilter->getEndDate()) {
				$queryBuilder->where('date BETWEEN UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 3 MONTH)) AND UNIX_TIMESTAMP(NOW())');
			}
		}

		// filter currency
		if($this->_filters->exists('currency')) {
			$currency = $this->_filters->get('currency');
			if($currency = $currency->getChoices()) {
				is_array($currency) ?
					$queryBuilder->where('Currency IN (?js)', [$currency]) :
					$queryBuilder->where('Currency = (?s)', [$currency])
				;
			}
		}

		// filter source
		if($this->_filters->exists('source')) {
			$source = $this->_filters->get('source');
			if($source = $source->getChoices()) {
				is_array($source) ?
					$queryBuilder->where('Source IN (?js)', [$source]) :
					$queryBuilder->where('Source = (?s)', [$source])
				;
			}
		}

		// filter type
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

	private function _dataTransform($data)
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
				ucwords($row->Type),
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