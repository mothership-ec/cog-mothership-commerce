<?php

namespace Message\Mothership\Commerce\Product\Stock\Notification\Replenished;

use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;

class Loader {

	protected $_query;

	protected $_returnArray;

	public function __construct(DB\Query $query)
	{
		$this->_query = $query;
	}

	public function getPending()
	{
		$result = $this->_query->run("
			SELECT
				notification_id
			FROM
				stock_notification
			WHERE
				type = 'replenished' AND
				notified_at IS NULL
		");

		return count($result) ? $this->_load($result->flatten(), true) : false;
	}

	public function _load($ids, $alwaysReturnArray = false)
	{
		if (! is_array($ids)) {
			$ids = (array) $ids;
		}

		if (! $ids) {
			return $alwaysReturnArray ? array() : false;
		}

		$result = $this->_query->run('
			SELECT
				*
			FROM
				stock_notification
			WHERE
				notification_id IN (?ij)
		', array($ids));

		if (0 === count($result)) {
			return $alwaysReturnArray ? array() : false;
		}

		$entities = $result->bindTo(
			'Message\\Mothership\\Commerce\\Product\\Stock\\Notification\\Replenished\\Notification'
		);
		$notifications = array();

		foreach ($entities as $key => $entity) {
			$entity->id = $result[$key]->notification_id;

			// Add created authorship
			$entity->authorship->create(
				new DateTimeImmutable(date('c', $result[$key]->created_at))//,
				// $result[$key]->created_by
			);

			$notifications[$entity->id] = $entity;
		}

		return count($notifications) == 1 && !$alwaysReturnArray ? array_shift($notifications) : $notifications;
	}

}