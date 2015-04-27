<?php

namespace Message\Mothership\Commerce\Report;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\Routing\UrlGenerator;
use Message\Cog\Event\DispatcherInterface;

use Message\Mothership\Report\Chart\TableChart;
use Message\Mothership\Report\Filter\DateRange;
use Message\Mothership\Report\Filter\Choices;

class SalesByOrder extends AbstractSales
{
	/**
	 * Constructor.
	 *
	 * @param QueryBuilderFactory   $builderFactory
	 * @param UrlGenerator          $routingGenerator
	 * @param DispatcherInterface   $eventDispatcher
	 */
	public function __construct(
		QueryBuilderFactory $builderFactory,
		UrlGenerator $routingGenerator,
		DispatcherInterface $eventDispatcher,
		array $currencies
	)
	{
		parent::__construct($builderFactory, $routingGenerator, $eventDispatcher, $currencies);
		$this->_setName('sales_by_order');
		$this->_setDisplayName('Sales by Order');
		$this->_setReportGroup('Sales');
		$this->_setDescription('
			This report displays all completed orders.
			By default it includes all data (orders, returns, shipping) from the last month (by completed date).
		');
		$startDate = new \DateTime;
		$this->getFilters()->get('date_range')->setStartDate($startDate->setTimestamp(strtotime(date('Y-m-d H:i')." -1 month")));
	}

	/**
	 * Set columns for use in reports.
	 *
	 * @return array  Returns array of columns as keys with format for Google Charts as the value.
	 */
	public function getColumns()
	{
		return [
			'Order'    => 'string',
			'Date'     => 'number',
			'User'     => 'string',
			'Source'   => 'string',
			'Currency' => 'string',
			'Net'      => 'number',
			'Tax'      => 'number',
			'Gross'    => 'number',
			'Country'  => 'string',
		];
	}

	/**
	 * Dispatches event to get all sales, returns & shipping queries.
	 *
	 * Unions all sub queries & creates parent query.
	 * Sum all totals and grouping by ORDER.
	 * Order by DATE.
	 *
	 * @return Query
	 */
	protected function _getQuery()
	{
		$fromQuery = $this->_getFilteredQuery();

		$queryBuilder = $this->_builderFactory->getQueryBuilder();
		$queryBuilder
			->select('date AS "UnixDate"')
			->select('FROM_UNIXTIME(date, "%d-%b-%Y %k:%i") AS "Date"')
			->select('totals.order_id AS "Order"')
			->select('totals.user_id AS "UserID"')
			->select('CONCAT(totals.user_surname,", ",totals.user_forename) AS "User"')
			->select('totals.source AS "Source"')
			->select('totals.currency AS "Currency"')
			->select('SUM(totals.net) AS "Net"')
			->select('SUM(totals.tax) AS "Tax"')
			->select('SUM(totals.gross) AS "Gross"')
			->select('totals.country AS "Country"')
			->from('totals', $fromQuery)
			->orderBy('order_id DESC')
			->groupBy('order_id')
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
	 * @return string|array  Returns data as string in JSON format or array.
	 */
	protected function _dataTransform($data, $output = null)
	{
		$result = [];

		if ($output === "json") {

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
					ucwords($row->Source),
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

		} else {

			foreach ($data as $row) {
				$result[] = [
					$row->Order,
					$row->Date,
					ucwords($row->User),
					ucwords($row->Source),
					$row->Currency,
					$row->Net,
					$row->Tax,
					$row->Gross,
					$row->Country
				];
			}
			return $result;
		}
	}
}