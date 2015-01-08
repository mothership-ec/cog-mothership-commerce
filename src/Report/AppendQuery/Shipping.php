<?php

namespace Message\Mothership\Commerce\Report\AppendQuery;

use Message\Cog\DB\QueryBuilderFactory;
use Message\Mothership\Report\Filter;
use Message\Mothership\Report\Report\AppendQuery\FilterableInterface;

class Shipping implements FilterableInterface
{
	private $_builderFactory;
	private $_filters;

	/**
	 * Constructor.
	 *
	 * @param QueryBuilderFactory   $builderFactory
	 */
	public function __construct(QueryBuilderFactory $builderFactory)
	{
		$this->_builderFactory = $builderFactory;
	}

	/**
	 * Sets the filters from the report.
	 *
	 * @param Filter\Collection $filters
	 *
	 * @return  $this  Return $this for chainability
	 */
	public function setFilters(Filter\Collection $filters)
	{
		$this->_filters = $filters;

		return $this;
	}

	/**
	 * Gets all SHIPPING data where:
	 * Order status is not CANCELLED (-300) or PAYMENT_PENDING (-100).
	 *
	 * All columns must match the other sub-queries used in SALES_REPORT.
	 * This because all subqueries are UNIONED together.
	 *
	 * @todo   Uncomment 'AND order_address.deleted_at IS NULL' when
	 *         deletable address functionality is merged.
	 *
	 * @return Query
	 */
	public function getQueryBuilder()
	{
		$data = $this->_builderFactory->getQueryBuilder();

		$data
			->select('order_summary.created_at AS "Date"')
			->select('order_summary.currency_id AS "Currency"')
			->select('IFNULL(net, 0) AS "Net"')
			->select('IFNULL(tax, 0) AS "Tax"')
			->select('IFNULL(gross, 0) AS "Gross"')
			->select('order_summary.type AS "Source"')
			->select('"Shipping" AS "Type"')
			->select('NULL AS "Item_ID"')
			->select('order_summary.order_id AS "Order_ID"')
			->select('NULL AS "Return_ID"')
			->select('NULL AS "Product_ID"')
			->select('display_name AS "Product"')
			->select('NULL AS "Option"')
			->select('country AS "Country"')
			->select('user.forename AS "User_Forename"')
			->select('user.surname AS "User_Surname"')
			->select('user.email AS "Email"')
			->select('order_summary.user_id AS "User_id"')
			->from('order_shipping')
			->join('order_summary', 'order_shipping.order_id = order_summary.order_id')
			->leftJoin('order_address', 'order_summary.order_id = order_address.order_id AND order_address.type = "delivery"') // AND order_address.deleted_at IS NULL
			->leftJoin('user', 'order_summary.user_id = user.user_id')
			->where('order_summary.status_code >= 0')
		;

		// Filter dates
		if($this->_filters->exists('date_range')) {

			$dateFilter = $this->_filters->get('date_range');

			if($date = $dateFilter->getStartDate()) {
				$data->where('order_summary.created_at > ?d', [$date->format('U')]);
			}

			if($date = $dateFilter->getEndDate()) {
				$data->where('order_summary.created_at < ?d', [$date->format('U')]);
			}
		}

		// Filter currency
		if($this->_filters->exists('currency')) {
			$currency = $this->_filters->get('currency');
			if($currency = $currency->getChoices()) {
				is_array($currency) ?
					$data->where('order_summary.currency_id IN (?js)', [$currency]) :
					$data->where('order_summary.currency_id = (?s)', [$currency])
				;
			}
		}

		// Filter source
		if($this->_filters->exists('source')) {
			$source = $this->_filters->get('source');
			if($source = $source->getChoices()) {
				is_array($source) ?
					$data->where('order_summary.type IN (?js)', [$source]) :
					$data->where('order_summary.type = (?s)', [$source])
				;
			}
		}

		return $data;
	}
}