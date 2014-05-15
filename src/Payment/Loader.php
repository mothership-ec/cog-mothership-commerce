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
class Loader implements Order\Transaction\RecordLoaderInterface
{
	protected $_query;
	protected $_methods;

	public function __construct(DB\Query $query, MethodCollection $methods)
	{
		$this->_query   = $query;
		$this->_methods = $methods;
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
		return $this->_load($id, false);
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
			return $alwaysReturnArray ? array() : false;
		}

		$result = $this->_query->run('
			SELECT
				*,
				payment_id AS id
			FROM
				payment
			WHERE
				payment_id IN (?ij)
		', array($ids));

		if (0 === count($result)) {
			return $alwaysReturnArray ? array() : false;
		}

		$entities = $result->bindTo('Message\\Mothership\\Commerce\\Payment\\Payment');
		$return   = array();

		foreach ($result as $key => $row) {
			// Cast decimals to float
			$entities[$key]->amount = (float) $row->amount;
			$entities[$key]->change = $row->change ? (float) $row->change : null;

			$entities[$key]->authorship->create(
				new DateTimeImmutable(date('c', $row->created_at)),
				$row->created_by
			);

			$entities[$key]->method = $this->_methods->get($row->method);

			$return[$row->id] = $entities[$key];
		}

		return $alwaysReturnArray || count($return) > 1 ? $return : reset($return);
	}
}