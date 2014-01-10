<?php

namespace Message\Mothership\Commerce\Order\Entity\Note;

use Message\Mothership\Commerce\Order;

use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Order note loader.
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
				note_id
			FROM
				order_note
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
				note_id           AS id,
				customer_notified AS customerNotified,
				raised_from       AS raisedFrom
			FROM
				order_note
			WHERE
				note_id IN (?ij)
		', array($ids));

		if (0 === count($result)) {
			return $alwaysReturnArray ? array() : false;
		}

		$notes  = $result->bindTo('Message\\Mothership\\Commerce\\Order\\Entity\\Note\\Note');
		$return = array();

		foreach ($result as $key => $row) {
			$notes[$key]->customerNotified = (bool) $row->customerNotified;

			$notes[$key]->authorship->create(
				new DateTimeImmutable(date('c', $row->created_at)),
				$row->created_by
			);

			if (!$order || $row->order_id != $order->id) {
				$order = $this->_orderLoader->getByID($row->order_id);
			}

			$notes[$key]->order = $order;

			$return[$row->id] = $notes[$key];
		}

		return $alwaysReturnArray || count($return) > 1 ? $return : reset($return);
	}
}