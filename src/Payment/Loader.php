<?php

namespace Message\Mothership\Commerce\Payment;

use Message\Mothership\Commerce\Order;

use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Payment loader.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Loader implements Order\Transaction\DeletableRecordLoaderInterface
{
	protected $_query;
	protected $_methods;
	protected $_includeDeleted = false;

	public function __construct(DB\Query $query, MethodCollection $methods)
	{
		$this->_query   = $query;
		$this->_methods = $methods;
	}

	/**
	 * Set whether to load deleted payments.
	 *
	 * @param  bool $bool True to load deleted payments, false otherwise
	 *
	 * @return Loader     Returns $this for chainability
	 */
	public function includeDeleted($bool = true)
	{
		$this->_includeDeleted = (bool) $bool;

		return $this;
	}

	/**
	 * Get payments for a particular method with a particular payment reference.
	 *
	 * @param  string|MethodInterface $method    The payment method or method name
	 * @param  string                 $reference The payment reference
	 *
	 * @return Payment|array[Payment]            Payment(s) for this method & reference
	 */
	public function getByMethodAndReference($method, $reference)
	{
		if ($method instanceof MethodInterface) {
			$method = $method->getName();
		}

		$result = $this->_query->run('
			SELECT
				payment_id
			FROM
				payment
			WHERE
				method    = :method?s
			AND reference = :reference?s
		', array(
			'method'    => $method,
			'reference' => $reference,
		));

		return $this->_load($result->flatten(), false);
	}

	public function getByID($id)
	{
		return $this->_load($id, is_array($id));
	}

	public function getByIDs(array $ids)
	{
		return $this->_load($ids, true);
	}

	/**
	 * Alias of getByID() for `Order\Transaction\RecordLoaderInterface`.
	 *
	 * @see getByID
	 *
	 * @param  int $id       The payment ID
	 *
	 * @return Payment|false The payment, or false if it doesn't exist
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
				payment_id  AS id,
				currency_id AS currencyID
			FROM
				payment
			WHERE
				payment_id IN (?ij)
			' . ($this->_includeDeleted ? '' : 'AND deleted_at IS NULL') . '
		', array($ids));

		if (0 === count($result)) {
			return $alwaysReturnArray ? [] : false;
		}

		$entities = $result->bindTo('Message\\Mothership\\Commerce\\Payment\\Payment');
		$return   = [];

		foreach ($result as $key => $row) {
			// Cast decimals to float
			$entities[$key]->amount = (float) $row->amount;
			$entities[$key]->change = $row->change ? (float) $row->change : null;

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

			$entities[$key]->method = $this->_methods->get($row->method);

			$return[$row->id] = $entities[$key];
		}

		return $alwaysReturnArray || count($return) > 1 ? $return : reset($return);
	}
}