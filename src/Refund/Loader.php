<?php

namespace Message\Mothership\Commerce\Refund;

use Message\Mothership\Commerce\Payment\Loader as PaymentLoader;
use Message\Mothership\Commerce\Order;
use Message\Mothership\Commerce\Order\Entity\Payment\MethodCollection;

use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Refund loader.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Loader implements Order\Transaction\RecordLoaderInterface
{
	protected $_query;
	protected $_methods;
	protected $_paymentLoader;
	protected $_includeDeleted = false;

	public function __construct(DB\Query $query, MethodCollection $paymentMethods, PaymentLoader $paymentLoader)
	{
		$this->_query         = $query;
		$this->_methods       = $paymentMethods;
		$this->_paymentLoader = $paymentLoader;
	}

	/**
	 * Toggle whether to load deleted refunds.
	 *
	 * @param  bool $bool True to load deleted refunds, false otherwise
	 *
	 * @return Loader     Returns $this for chainability
	 */
	public function includeDeleted($bool = true)
	{
		$this->_includeDeleted = (bool) $bool;

		return $this;
	}

	public function getByID($id)
	{
		return $this->_load($id, false);
	}

	/**
	 * Alias of getByID() for `Order\Transaction\RecordLoaderInterface`.
	 *
	 * @see getByID
	 *
	 * @param  int $id      The refund ID
	 *
	 * @return Refund|false The refund, or false if it doesn't exist
	 */
	public function getByRecordID($id)
	{
		return $this->getByID($id);
	}

	protected function _load($ids, $alwaysReturnArray = false)
	{
		if (!is_array($ids)) {
			$ids = (array) $ids;
		}

		if (!$ids) {
			return $alwaysReturnArray ? [] : false;
		}

		$result = $this->_query->run('
			SELECT
				*,
				refund_id AS id
			FROM
				refund
			WHERE
				refund_id IN (?ij)
			' . ($this->_includeDeleted ? '' : 'AND deleted_at IS NULL') . '
		', array($ids));

		if (0 === count($result)) {
			return $alwaysReturnArray ? [] : false;
		}

		$entities = $result->bindTo('Message\\Mothership\\Commerce\\Refund\\Refund');
		$return   = [];

		foreach ($result as $key => $row) {
			// Cast decimals to float
			$entities[$key]->amount = (float) $row->amount;

			$entities[$key]->authorship->create(
				new DateTimeImmutable(date('c', $row->created_at)),
				$row->created_by
			);

			if ($row->deleted_at) {
				$entities[$key]->authorship->delete(
					new DateTimeImmutable(date('c', $row->deleted_at)),
					$row->deleted_by
				);
			}

			if ($row->payment_id) {
				$entities[$key]->payment = $this->_paymentLoader->getByID($row->payment_id);
			}

			$entities[$key]->method = $this->_methods->get($row->method);

			$return[$row->id] = $entities[$key];
		}

		return $alwaysReturnArray || count($return) > 1 ? $return : reset($return);
	}
}