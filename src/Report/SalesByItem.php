<?php

namespace Message\Mothership\Commerce\Report;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\Localisation\Translator;
use Message\Cog\Routing\UrlGenerator;
use Message\Cog\Event\DispatcherInterface;

use Message\Mothership\Report\Chart\TableChart;
use Message\Mothership\Report\Filter\DateRangeFilter;

class SalesByItem extends AbstractSales
{
	public function __construct(QueryBuilderFactory $builderFactory, Translator $trans, UrlGenerator $routingGenerator, DispatcherInterface $eventDispatcher)
	{
		parent::__construct($builderFactory, $trans, $routingGenerator, $eventDispatcher);
		$this->name = 'sales_by_item';
		$this->displayName = 'Sales by Item';
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
			['type' => 'number', 'name' => "Date",     ],
			['type' => 'string', 'name' => "Order",    ],
			['type' => 'string', 'name' => "Return",   ],
			['type' => 'string', 'name' => "Source",   ],
			['type' => 'string', 'name' => "Type",     ],
			['type' => 'string', 'name' => "Product",  ],
			['type' => 'string', 'name' => "Option",   ],
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
			->select('date AS "UnixDate"')
			->select('FROM_UNIXTIME(date, "%d-%b-%Y %H:%m") AS "Date"')
			->select('totals.order_id AS "Order"')
			->select('totals.return_id AS "Return"')
			->select('totals.item_id AS "Item"')
			->select('totals.source AS "Source"')
			->select('totals.type AS "Type"')
			->select('totals.product_id AS "Product_ID"')
			->select('totals.product AS "Product"')
			->select('totals.option AS "Option"')
			->select('totals.currency AS "Currency"')
			->select('totals.net AS "Net"')
			->select('totals.tax AS "Tax"')
			->select('totals.gross AS "Gross"')
			->from('totals', $fromQuery)
			->orderBy('UnixDate DESC')
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
				[
					'v' => (float) $row->UnixDate,
					'f' => (string) $row->Date
				],
				'<a href ="'.$this->generateUrl('ms.commerce.order.detail.view', ['orderID' => (int) $row->Order]).'">'.$row->Order.'</a>',
				$row->Return ?
					'<a href ="'.$this->generateUrl('ms.commerce.return.view', ['returnID' => (int) $row->Return]).'">'.$row->Return.'</a>'
					: "",
				ucwords($row->Source),
				ucwords($row->Type),
				$row->Product_ID ?
					[
						'v' => ucwords($row->Product),
						'f' => (string) '<a href ="'.$this->generateUrl('ms.commerce.product.edit.attributes', ['productID' => $row->Product_ID]).'">'
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
			];
		}

		return json_encode($result);
	}
}