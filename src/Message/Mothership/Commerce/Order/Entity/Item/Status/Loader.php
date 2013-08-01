<?php

namespace Message\Mothership\Commerce\Order\Entity\Item\Status;

use Message\Mothership\Commerce\Order;
use Message\Mothership\Commerce\Order\Entity\Item;

use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Order item status loader.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Loader
{
	protected $_query;
	protected $_statuses;

	public function __construct(DB\Query $query, Order\Status\Collection $statuses)
	{
		$this->_query    = $query;
		$this->_statuses = $statuses;
	}

	/**
	 * Finds the latest status for a specific item, and sets it as the 'status'
	 * property on the item.
	 *
	 * @param  Item\Item $item The item to set the latest status for
	 *
	 * @return Item\Item       The item with the latest status set
	 */
	public function setLatest(Item\Item $item)
	{
		$result = $this->_load($this->_query->run('
			SELECT
				*
			FROM
				order_item_status
			WHERE
				item_id = ?i
			ORDER BY
				created_at DESC
			LIMIT 1
		', $item->id));

		$item->status = array_shift($result);

		return $item;
	}

	/**
	 * Get a full history of statuses for a specific item, with the latest
	 * status first.
	 *
	 * @param  Item\Item $item The item to get the status history for
	 *
	 * @return array[Status]   Array of statuses, latest first
	 */
	public function getHistory(Item\Item $item)
	{
		return $this->_load($this->_query->run('
			SELECT
				*
			FROM
				order_item_status
			WHERE
				item_id = ?i
			ORDER BY
				created_at DESC
		', $item->id));
	}

	protected function _load(DB\Result $result)
	{
		$return = array();

		foreach ($result as $row) {
			$status = $this->_statuses->get($row->status_code);
			$status = new Status($status->code, $status->name);

			$status->authorship->create(
				new DateTimeImmutable(date('c', $row->created_at)),
				$row->created_by ?: null
			);

			$return[] = $status;
		}

		return $return;
	}
}