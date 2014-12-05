<?php

namespace Message\Mothership\Commerce\Report;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\Localisation\Translator;
use Message\Cog\Routing\UrlGenerator;
use Message\Cog\Event\DispatcherInterface;

use Message\Mothership\Report\Chart\TableChart;
use Message\Mothership\Report\Filter\DateRangeFilter;

class SalesByOrder extends AbstractSales
{
	public function __construct(QueryBuilderFactory $builderFactory, Translator $trans, UrlGenerator $routingGenerator, DispatcherInterface $eventDispatcher)
	{
		parent::__construct($builderFactory, $trans, $routingGenerator, $eventDispatcher);
		$this->name = 'sales_by_order';
		$this->displayName = 'Sales by Order';
		$this->reportGroup = "Sales";
		$this->_charts = [new TableChart];
		$this->_filters->add(new DateRangeFilter);
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
		if($this->_filters->exists('filter_date')) {
			$dateFilter = $this->_filters->get('filter_date');

			if($date = $dateFilter->getStartDate()) {
				$queryBuilder->where('date > ?d', [$date->format('U')]);
			}

			if($date = $dateFilter->getEndDate()) {
				$queryBuilder->where('date < ?d', [$date->format('U')]);
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