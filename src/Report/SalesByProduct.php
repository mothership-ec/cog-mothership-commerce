<?php

namespace Message\Mothership\Commerce\Report;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\Localisation\Translator;
use Message\Cog\Routing\UrlGenerator;
use Message\Cog\Event\DispatcherInterface;

use Message\Mothership\Report\Chart\TableChart;

class SalesByProduct extends AbstractSales
{
	public function __construct(QueryBuilderFactory $builderFactory, Translator $trans, UrlGenerator $routingGenerator, DispatcherInterface $eventDispatcher)
	{
		$this->name = 'sales_by_product';
		$this->displayName = 'Sales by Product';
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
			['type' => 'string', 'name' => "Product",     ],
			['type' => 'string', 'name' => "Option",      ],
			['type' => 'string', 'name' => "Currency",    ],
			['type' => 'number', 'name' => "Net",         ],
			['type' => 'number', 'name' => "Tax",         ],
			['type' => 'number', 'name' => "Gross",       ],
			['type' => 'number', 'name' => "Number Sold", ],
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
			->select('totals.product_id AS "ID"')
			->select('totals.product AS "Product"')
			->select('totals.option AS "Option"')
			->select('totals.currency AS "Currency"')
			->select('SUM(totals.net) AS "Net"')
			->select('SUM(totals.tax) AS "Tax"')
			->select('SUM(totals.gross) AS "Gross"')
			->select('COUNT(totals.gross) AS "NumberSold"')
			->from('totals', $fromQuery)
			->where('product_id IS NOT NULL')
			->orderBy('gross DESC')
			->groupBy('product_id, currency')
		;

		//de($queryBuilder->getQuery())->length(-1);

		return $queryBuilder->getQuery();
	}

	private function _dataTransform($data)
	{
		$result = [];

		foreach ($data as $row) {
			$result[] = [
				$row->ID ?
					[
						'v' => ucwords($row->Product),
						'f' => (string) '<a href ="'.$this->generateUrl('ms.commerce.product.edit.attributes', ['productID' => $row->ID]).'">'
						.ucwords($row->Product).'</a>'
					]
					: $row->Product,
				ucwords($row->Option),
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
				$row->NumberSold,
			];
		}

		return json_encode($result);
	}
}