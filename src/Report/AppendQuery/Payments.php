<?php

namespace Message\Mothership\Commerce\Report\AppendQuery;

use Message\Cog\DB\QueryBuilderFactory;
use Message\Mothership\Report\Filter;
use Message\Mothership\Report\Report\AppendQuery\FilterableInterface;

class Payments implements FilterableInterface
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
	 * Gets all PAYMENT data.
	 *
	 * All columns must match the other sub-queries used in TRANSACTIONS_REPORT.
	 * This because all subqueries are UNIONED together.
	 *
	 * @return Query
	 */
	public function getQueryBuilder()
	{
		$data = $this->_builderFactory->getQueryBuilder();

		$data
			->select('payment.payment_id AS ID')
			->select('payment.created_at AS date')
			->select('payment.created_by AS user_id')
			->select('CONCAT(user.surname, ", ", user.forename) AS user')
			->select('currency_id as currency')
			->select('method')
			->select('amount')
			->select('"Payment" AS type')
			->select('order_id AS order_return_id')
			->select('reference')
			->from('payment')
			->leftJoin('order_payment','payment.payment_id = order_payment.payment_id')
			->join('user','user.user_id = payment.created_by');

		// Filter dates
		if($this->_filters->exists('date_range')) {

			$dateFilter = $this->_filters->get('date_range');

			if($date = $dateFilter->getStartDate()) {
				$data->where('payment.created_at > ?d', [$date->format('U')]);
			}

			if($date = $dateFilter->getEndDate()) {
				$data->where('payment.created_at < ?d', [$date->format('U')]);
			}
		}

		return $data;
	}
}
