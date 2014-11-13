<?php

namespace Message\Mothership\Commerce\Report;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\Localisation\Translator;
use Message\Cog\Routing\UrlGenerator;
use Message\Cog\Event\DispatcherInterface;

use Message\Mothership\Report\Chart\TableChart;

class SalesByLocation extends AbstractSales
{
	public function __construct(QueryBuilderFactory $builderFactory, Translator $trans, UrlGenerator $routingGenerator, DispatcherInterface $eventDispatcher)
	{
		$this->name = 'sales_by_location';
		$this->displayName = 'Sales by Location';
		$this->reportGroup = "Sales";
		$this->_charts = [new TableChart];
		parent::__construct($builderFactory, $trans, $routingGenerator, $eventDispatcher);
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
			['type' => 'string', 'name' => "Country",  ],
			['type' => 'string', 'name' => "Currency", ],
			['type' => 'number', 'name' => "Net",      ],
			['type' => 'number', 'name' => "Tax",      ],
			['type' => 'number', 'name' => "Gross",    ],
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
			->select('totals.country AS "Country"')
			->select('totals.currency AS "Currency"')
			->select('SUM(totals.net) AS "Net"')
			->select('SUM(totals.tax) AS "Tax"')
			->select('SUM(totals.gross) AS "Gross"')
			->from('totals', $fromQuery)
			->where('country IS NOT NULL')
			->groupBy('country, currency')
			->orderBy('SUM(totals.gross) DESC')
		;

		return $queryBuilder->getQuery();
	}

	private function _dataTransform($data)
	{
		$result = [];

		foreach ($data as $row) {
			$result[] = [
				$row->Country,
				$row->Currency,
				[ 'v' => (float) $row->Net, 'f' => (string) number_format($row->Net,2,'.',',')],
				[ 'v' => (float) $row->Tax, 'f' => (string) number_format($row->Tax,2,'.',',')],
				[ 'v' => (float) $row->Gross, 'f' => (string) number_format($row->Gross,2,'.',',')],
			];
		}

		return json_encode($result);
	}
}