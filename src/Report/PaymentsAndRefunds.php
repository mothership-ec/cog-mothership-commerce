<?php

namespace Message\Mothership\Commerce\Report;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\Localisation\Translator;
use Message\Cog\Routing\UrlGenerator;
use Message\Cog\Event\DispatcherInterface;

use Message\Mothership\Report\Chart\TableChart;
use Message\Mothership\Report\Filter\DateRange;

class PaymentsAndRefunds extends AbstractTransactions
{
	public function __construct(QueryBuilderFactory $builderFactory, Translator $trans, UrlGenerator $routingGenerator, DispatcherInterface $eventDispatcher)
	{
		parent::__construct($builderFactory, $trans, $routingGenerator, $eventDispatcher);
		$this->name        = 'payments_refunds';
		$this->displayName = 'Payments & Refunds';
		$this->reportGroup = 'Transactions';
		$this->_charts[]   = new TableChart;
		$this->_filters->add(new DateRange);
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
			['type' => 'string', 'name' => "Date",        ],
			['type' => 'string', 'name' => "User",        ],
			['type' => 'string', 'name' => "Currency",    ],
			['type' => 'string', 'name' => "Method",      ],
			['type' => 'number', 'name' => "Amount",      ],
			['type' => 'string', 'name' => "Type",        ],
			['type' => 'string', 'name' => "Order/Return",],
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
			->select('*')
			->from('t1',$fromQuery)
			->orderBy('created_at DESC')
			->limit('25')
		;

		// filter dates
		if($this->_filters->exists('date_range')) {
			$dateFilter = $this->_filters->get('date_range');
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

			if ($row->type == "Payment") {
				$url = $this->generateUrl('ms.commerce.order.detail.view', ['orderID' => (int) $row->order_return_id]);
			} else {
				$url = $this->generateUrl('ms.commerce.return.view', ['returnID' => (int) $row->order_return_id]);
			}

			$result[] = [
				date('Y-m-d H:i', $row->created_at),
				$row->user ?
					[
						'v' => utf8_encode($row->user),
						'f' => 	(string) '<a href ="'.$this->generateUrl('ms.cp.user.admin.detail.edit', ['userID' => $row->user_id]).'">'.
								ucwords(utf8_encode($row->user)).'</a>'
					]
					: $row->user,
				$row->currency,
				$row->method,
				[
					'v' => (float) $row->amount,
					'f' => (string) number_format($row->amount,2,'.',',')
				],
				$row->type,
				'<a href ="'.$url.'">'.$row->order_return_id.'</a>',
			];
		}

		return json_encode($result);
	}
}