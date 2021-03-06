<?php

namespace Message\Mothership\Commerce\Order\Entity\Dispatch;

use Message\Mothership\Commerce\Order;

use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Order dispatch loader.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Loader extends Order\Entity\BaseLoader
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
	 * Set whether to load deleted dispatches. Also sets include deleted on order loader.
	 *
	 * @param  bool $bool    true / false as to whether to include deleted dispatches
	 *
	 * @return Loader        Loader object in order to chain the methods
	 */
	public function includeDeleted($bool = true)
	{
		$this->_includeDeleted = (bool) $bool;
		$this->_orderLoader->includeDeleted($this->_includeDeleted);

		return $this;
	}

	public function getByID($id, Order\Order $order = null)
	{
		return $this->_load($id, false, $order);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getByOrder(Order\Order $order)
	{
		$result = $this->_query->run('
			SELECT
				dispatch_id
			FROM
				order_dispatch
			WHERE
				order_id = ?i
		', $order->id);

		return $this->_load($result->flatten(), true, $order);
	}

	public function getUnpostaged($method = null)
	{
		if ($method instanceof MethodInterface) {
			$method = $method->getName();
		}

		if ($method) {
			$result = $this->_query->run('
				SELECT
					dispatch_id
				FROM
					order_dispatch
				WHERE
					code IS NULL
				AND shipped_at IS NULL
				AND method = ?s
			', array($method));
		}
		else {
			$result = $this->_query->run('
				SELECT
					dispatch_id
				FROM
					order_dispatch
				WHERE
					code IS NULL
				AND shipped_at IS NULL
			');
		}

		return $this->_load($result->flatten(), true);
	}

	public function getPostagedUnshipped($method = null)
	{
		if ($method instanceof MethodInterface) {
			$method = $method->getName();
		}

		if ($method) {
			$result = $this->_query->run('
				SELECT
					dispatch_id
				FROM
					order_dispatch
				WHERE
					code IS NOT NULL
				AND shipped_at IS NULL
				AND method = ?s
			', array($method));
		}
		else {
			$result = $this->_query->run('
				SELECT
					dispatch_id
				FROM
					order_dispatch
				WHERE
					code IS NOT NULL
				AND shipped_at IS NULL
			');
		}

		return $this->_load($result->flatten(), true);
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
				dispatch_id AS id,
				shipped_at  AS shippedAt,
				shipped_by  AS shippedBy
			FROM
				order_dispatch
			WHERE
				dispatch_id IN (?ij)
			' . $includeDeleted . '
		', array($ids));

		if (0 === count($result)) {
			return $alwaysReturnArray ? array() : false;
		}

		$entities = $result->bindTo('Message\\Mothership\\Commerce\\Order\\Entity\\Dispatch\\Dispatch');
		$return   = array();

		foreach ($result as $key => $row) {
			// Cast decimals to float (unless they're null)
			$entities[$key]->cost = is_null($row->cost) ? null : (float) $row->cost;

			$entities[$key]->authorship->create(
				new DateTimeImmutable(date('c', $row->created_at)),
				$row->created_by
			);

			if ($row->updated_at) {
				$items[$key]->authorship->update(
					new DateTimeImmutable(date('c', $row->updated_by)),
					$row->updated_by
				);
			}

			if ($row->shippedAt) {
				$entities[$key]->shippedAt = new DateTimeImmutable(date('c', $row->shippedAt));
			}

			if (!$order || $row->order_id != $order->id) {
				$order = $this->_orderLoader->getByID($row->order_id);
			}

			$entities[$key]->order = $order;

			$entities[$key]->method = $this->_methods->get($row->method);

			// Get the items in this dispatch
			$items = $this->_query->run('
				SELECT
					item_id
				FROM
					order_dispatch_item
				WHERE
					dispatch_id = ?i
			', $row->id);

			foreach ($items->flatten() as $item) {
				$entities[$key]->items->append($entities[$key]->order->items->get($item));
			}

			$return[$row->id] = $entities[$key];
		}

		return $alwaysReturnArray || count($return) > 1 ? $return : reset($return);
	}

}