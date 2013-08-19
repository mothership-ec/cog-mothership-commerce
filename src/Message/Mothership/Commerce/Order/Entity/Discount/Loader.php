<?php

namespace Message\Mothership\Commerce\Order\Entity\Discount;

use Message\Mothership\Commerce\Order;

use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Order discount loader.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Loader extends Order\Entity\BaseLoader
{
	protected $_query;

	public function __construct(DB\Query $query)
	{
		$this->_query = $query;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getByOrder(Order\Order $order)
	{
		$result = $this->_query->run('
			SELECT
				discount_id
			FROM
				order_discount
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

		$result = $this->_query->run('
			SELECT
				*,
				discount_id AS id
			FROM
				order_discount
			WHERE
				discount_id IN (?ij)
		', array($ids));

		if (0 === count($result)) {
			return $alwaysReturnArray ? array() : false;
		}

		$entities = $result->bindTo('Message\\Mothership\\Commerce\\Order\\Entity\\Discount\\Discount');
		$return   = array();

		foreach ($result as $key => $row) {
			// Cast decimals to float
			$entities[$key]->amount     = (float) $row->amount;
			$entities[$key]->percentage = (float) $row->percentage;

			$entities[$key]->authorship->create(
				new DateTimeImmutable(date('c', $row->created_at)),
				$row->created_by
			);

			if (!$order) {
				$order = $this->_orderLoader->getByID($row->order_id);
			}

			$entities[$key]->order = $order;

			// TODO: set the campaign if there's a code we can find

			// Get the items that this discount applies to
			$items = $this->_query->run('
				SELECT
					item_id
				FROM
					order_item_discount
				WHERE
					discount_id = ?i
			', $row->id);

			foreach ($items->flatten() as $item) {
				$entities[$key]->items = $entities[$key]->order->items->get($item);
			}

			// If the discount doesn't apply to specific items, it applies to all items
			if (empty($entities[$key])) {
				$entities[$key]->items = $entities[$key]->order->items->all();
			}

			$return[$row->id] = $entities[$key];
		}

		return $alwaysReturnArray || count($return) > 1 ? $return : reset($return);
	}

}