<?php

namespace Message\Mothership\Commerce\Order\Entity\Refund;

use Message\Mothership\Commerce\Order;
use Message\Mothership\Commerce\Order\Entity\Payment\MethodCollection;

use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Order refund loader.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Loader extends Order\Entity\BaseLoader
{
	protected $_query;
	protected $_methods;
	protected $_includeDeleted;

	public function __construct(DB\Query $query, MethodCollection $paymentMethods)
	{
		$this->_query   = $query;
		$this->_methods = $paymentMethods;
	}

	/**
	 * Toggle whether or not to load deleted refunds
	 *
	 * @param bool $bool    true / false as to whether to include deleted refunds
	 * @return Loader       Loader object in order to chain the methods
	 */
	public function includeDeleted($bool)
	{
		$this->_includeDeleted = $bool;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getByOrder(Order\Order $order)
	{
		$result = $this->_query->run('
			SELECT
				refund_id
			FROM
				order_refund
			WHERE
				order_id = ?i
		', $order->id);

		return $this->_load($result->flatten(), true, $order);
	}

	public function getByID($id, Order\Order $order = null)
	{
		return $this->_load($id, false, $order);
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
				refund_id AS id
			FROM
				order_refund
			WHERE
				refund_id IN (?ij)
			' . $includeDeleted . '
		', array($ids));

		if (0 === count($result)) {
			return $alwaysReturnArray ? array() : false;
		}

		$entities = $result->bindTo('Message\\Mothership\\Commerce\\Order\\Entity\\Refund\\Refund');
		$return   = array();

		foreach ($result as $key => $row) {
			// Cast decimals to float
			$entities[$key]->amount = (float) $row->amount;

			$entities[$key]->authorship->create(
				new DateTimeImmutable(date('c', $row->created_at)),
				$row->created_by
			);

			if (!$order || $row->order_id != $order->id) {
				$order = $this->_orderLoader->getByID($row->order_id);
			}

			$entities[$key]->order = $order;

			if ($row->payment_id) {
				$entities[$key]->payment = $entities[$key]->order->payments->get($row->payment_id);
			}

			// TODO: set return, if defined

			$entities[$key]->method = $this->_methods->get($row->method);

			$return[$row->id] = $entities[$key];
		}

		return $alwaysReturnArray || count($return) > 1 ? $return : reset($return);
	}

}