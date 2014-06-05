<?php

namespace Message\Mothership\Commerce\Order\Entity\Payment;

use Message\Mothership\Commerce\Order;

use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Order payment loader.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Loader extends Order\Entity\BaseLoader implements
	Order\Transaction\DeletableRecordLoaderInterface,
	Order\Entity\DeletableLoaderInterface
{
	protected $_query;
	protected $_paymentLoader;

	public function __construct(DB\Query $query, MethodCollection $methods)
	{
		$this->_query   = $query;
		$this->_methods = $methods;
	}

	/**
	 * Toggle whether to load deleted payments
	 *
	 * @param  bool $bool    true / false as to whether to include deleted payments
	 *
	 * @return Loader        Loader object in order to chain the methods
	 */
	public function includeDeleted($bool)
	{
		$this->_paymentLoader->includeDeleted((bool) $bool);
		$this->_orderLoader->includeDeleted((bool) $bool);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getByOrder(Order\Order $order)
	{
		$result = $this->_query->run('
			SELECT
				payment_id
			FROM
				order_payment
			WHERE
				order_id = ?i
		', $order->id);

		return $this->_load($result->flatten(), true, $order);
	}

	/**
	 * Get payments for a particular method with a particular payment reference.
	 *
	 * @param  string|MethodInterface $method The payment method or method name
	 * @param  string              $reference The payment reference
	 *
	 * @return Payment|array[Payment]         Payment(s) for this method & reference
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
				order_payment
			WHERE
				method    = :method?s
			AND reference = :reference?s
		', array(
			'method'    => $method,
			'reference' => $reference,
		));

		return $this->_load($result->flatten(), false);
	}

	public function getByID($id, Order\Order $order = null)
	{
		return $this->_load($id, false, $order);
	}

	/**
	 * Alias of getByID for Order\Transaction\RecordLoaderInterface
	 * @param  int $id record id
	 * @return Payment|false The payment, or false if it doesn't exist
	 */
	public function getByRecordID($id)
	{
		return $this->getByID($id);
	}

	protected function _load($ids, $alwaysReturnArray = false, Order\Order $order = null)
	{
		if (!is_array($ids)) {
			$ids = (array) $ids;
		}

		if (!$ids) {
			return $alwaysReturnArray ? array() : false;
		}

		$includeDeleted = $this->_includeDeleted ? '' : 'AND deleted_at IS NULL' ;

		$result = $this->_query->run('
			SELECT
				*,
				payment_id AS id
			FROM
				order_payment
			WHERE
				payment_id IN (?ij)
			' . $includeDeleted . '
		', array($ids));

		if (0 === count($result)) {
			return $alwaysReturnArray ? array() : false;
		}

		$entities = $result->bindTo('Message\\Mothership\\Commerce\\Order\\Entity\\Payment\\Payment');
		$return   = array();

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

			if (!$order || $row->order_id != $order->id) {
				$order = $this->_orderLoader->getByID($row->order_id);
			}

			$entities[$key]->order = $order;

			// TODO: set the return here if a return_id is set

			$entities[$key]->method = $this->_methods->get($row->method);

			$return[$row->id] = $entities[$key];
		}

		return $alwaysReturnArray || count($return) > 1 ? $return : reset($return);
	}

}