<?php

namespace Message\Mothership\Commerce\Report;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\Routing\UrlGenerator;
use Message\Cog\Event\DispatcherInterface;

use Message\Mothership\Report\Chart\TableChart;
use Message\Mothership\Report\Filter\DateRange;
use Message\Mothership\Report\Filter\Choices;


class PaymentsAndRefunds extends AbstractTransactions
{
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
		$this->name        = 'payments_refunds';
		$this->displayName = 'Payments & Refunds';
		$this->reportGroup = 'Transactions';
		$this->description =
			"This report displays all payments & refunds.
			By default it includes all data from the last month (by completed date).";
		$this->_charts[]   = new TableChart;
		$this->_filters->add(new DateRange);
		// Params for Choices filter: unique filter name, label, choices, multi-choice
		$this->_filters->add(new Choices(
			"type",
			"Type",
			[
				'payment' => 'Payment',
				'refund' => 'Refund',
			],
			false
		));
	}

	/**
	 * Retrieves JSON representation of the data and columns.
	 * Applies data to chart types set on report.
	 *
	 * @return Array  Returns all types of chart set on report with appropriate data.
	 */
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

	/**
	 * Set columns for use in reports.
	 *
	 * @return String  Returns columns in JSON format.
	 */
	public function getColumns()
	{
		$columns = [
			['type' => 'string', 'name' => "Date",        ],
			['type' => 'string', 'name' => "Created by",  ],
			['type' => 'string', 'name' => "Currency",    ],
			['type' => 'string', 'name' => "Method",      ],
			['type' => 'number', 'name' => "Amount",      ],
			['type' => 'string', 'name' => "Type",        ],
			['type' => 'string', 'name' => "Order/Return",],
		];

		return json_encode($columns);
	}

	/**
	 * Dispatches event to get all payment & refund queries.
	 *
	 * Unions all sub queries & creates parent query.
	 * Order by DATE.
	 *
	 * @return Query
	 */
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
			->orderBy('date DESC')
		;

		if($this->_filters->count('date_range')) {

			$dateFilter = $this->_filters->get('date_range');

			if($date = $dateFilter->getStartDate()) {
				$queryBuilder->where('date > ?d', [$date->format('U')]);
			}

			if($date = $dateFilter->getEndDate()) {
				$queryBuilder->where('date < ?d', [$date->format('U')]);
			}

			if(!$dateFilter->getStartDate() && !$dateFilter->getEndDate()) {
				$queryBuilder->where('date BETWEEN UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 MONTH)) AND UNIX_TIMESTAMP(NOW())');
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

	/**
	 * Takes the data and transforms it into a useable format.
	 *
	 * @param  $data    DB\Result  The data from the report query.
	 * @param  $output  String     The type of output required.
	 *
	 * @return String|Array  Returns columns as string in JSON format or array.
	 */
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
				date('Y-m-d H:i', $row->date),
				$row->user ?
					[
						'v' => utf8_encode($row->user),
						'f' => 	(string) '<a href ="'.$this->generateUrl('ms.cp.user.admin.detail.edit', ['userID' => $row->user_id]).'">'.
								ucwords(utf8_encode($row->user)).'</a>'
					]
					: $row->user,
				$row->currency,
				ucwords($row->method),
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