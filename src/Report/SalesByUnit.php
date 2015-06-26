<?php

namespace Message\Mothership\Commerce\Report;

use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\Routing\UrlGenerator;
use Message\Cog\Event\DispatcherInterface;

/**
 * Class SalesByUnit
 * @package Message\Mothership\Commerce\Report
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 */
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

	/**
	 * {@inheritDoc}
	 */
	public function getColumns()
	{
		return [
			'Product'      => 'string',
			'Option'       => 'string',
			'Currency'     => 'string',
			'Net'          => 'number',
			'Tax'          => 'number',
			'Gross'        => 'number',
			'Transactions' => 'number',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function _getQuery()
	{
		$fromQuery = $this->_getFilteredQuery();

		$queryBuilder = $this->_builderFactory->getQueryBuilder();
		$queryBuilder
			->select('totals.product_id AS "Product_ID"')
			->select('totals.product AS "Product"')
			->select('totals.option AS "Option"')
			->select('totals.currency AS "Currency"')
			->select('SUM(totals.net) AS "Net"')
			->select('SUM(totals.tax) AS "Tax"')
			->select('SUM(totals.gross) AS "Gross"')
			->select('COUNT(totals.gross) AS "Transactions"')
			->from('totals', $fromQuery)
			->orderBy('totals.gross DESC')
			// Group by product name and option string as unioned 'totals' parent query does not get unit ID
			->groupBy('totals.product, totals.option, currency')
		;

		if ($this->_filters->exists('type')) {

			$type = $this->_filters->get('type');

			if ($type = $type->getChoices()) {
				is_array($type) ?
					$queryBuilder->where('Type IN (?js)', [$type]) :
					$queryBuilder->where('Type = ?s', [$type])
				;
			}
		}

		return $queryBuilder->getQuery();
	}

	/**
	 * {@inheritDoc}
	 */
	protected function _dataTransform($data, $output = null)
	{
		$result = [];

		if ($output === 'json') {
			foreach ($data as $row) {
				$result[] = [
					$row->Product_ID ?
						[
							'v' => ucwords($row->Product),
							'f' => (string) '<a href ="'.$this->generateUrl('ms.commerce.product.edit.attributes', ['productID' => $row->Product_ID]).'">'
								.ucwords($row->Product).'</a>'
						]
						: $row->Product,
					$row->Option,
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
					(int) $row->Transactions,
				];
			}

			return json_encode($result);
		} else {
			foreach ($data as $row) {
				$result[] = [
					ucwords($row->Product),
					$row->Option,
					$row->Currency,
					$row->Net,
					$row->Gross,
					$row->Transactions
				];
			}

			return $result;
		}
	}
}