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
		$this->_setName('payments_refunds');
		$this->_setDisplayName('Payments & Refunds');
		$this->_setReportGroup('Transactions');
		$this->_setDescription('
			This report displays all payments & refunds.
			By default it includes all data from the last month (by completed date).
		');
		$this->_charts[]   = new TableChart;
		$this->_filters->add(new DateRange);
		$startDate = new \DateTime;
		$this->getFilters()->get('date_range')->setStartDate($startDate->setTimestamp(strtotime(date('Y-m-d H:i')." -1 month")));
		// Params for Choices filter: unique filter name, label, choices, multi-choice
		$this->_filters->add(new Choices(
			"type",
			"Type",
			[
				'Payment',
				'Refund',
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
		$data = $this->_dataTransform($this->_getQuery()->run(), "json");
		$columns = $this->_parseColumns($this->getColumns());

		foreach ($this->_charts as $chart) {
			$chart->setColumns($columns);
			$chart->setData($data);
		}

		return $this->_charts;
	}

	/**
	 * Set columns for use in reports.
	 *
	 * @return array  Returns array of columns as keys with format for Google Charts as the value.
	 */
	public function getColumns()
	{
		return [
			'Date'         => 'string',
			'Created by'   => 'string',
			'Currency'     => 'string',
			'Method'       => 'string',
			'Amount'       => 'number',
			'Type'         => 'string',
			'Order/Return' => 'string',
		];
	}

	/**
	 * Dispatches event to get all payment & refund queries.
	 *
	 * Unions all sub queries & creates parent query.
	 * Order by DATE.
	 *
	 * @return Query
	 */
	protected function _getQuery()
	{
		$unions = $this->_dispatchEvent($this->getFilters())->getQueryBuilders();

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

		// Filter type
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
	protected function _dataTransform($data, $output = null)
	{
		$result = [];

		if ($output === "json") {

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

		} else {

			foreach ($data as $row) {
				$result[] = [
					date('Y-m-d H:i', $row->date),
					$row->user,
					$row->currency,
					ucwords($row->method),
					$row->amount,
					$row->type,
					$row->order_return_id
				];
			}
			return $result;
		}
	}
}