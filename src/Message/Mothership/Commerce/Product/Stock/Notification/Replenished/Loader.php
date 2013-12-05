<?php

namespace Message\Mothership\Commerce\Product\Stock\Notification\Replenished;

use Message\Cog\DB\Query;
use Message\Cog\ValueObject\DateTimeImmutable;

class Loader
{
	protected $_query;
	protected $_stockLocations;
	protected $_userLoader;
	protected $_unitLoader;

	protected $_returnArray;

	public function __construct(Query $query, $stockLocations, $userLoader, $unitLoader)
	{
		$this->_query          = $query;
		$this->_stockLocations = $stockLocations;
		$this->_userLoader     = $userLoader;
		$this->_unitLoader     = $unitLoader;
	}

	public function getUnnotified()
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

	public function getPending()
	{
		$result = $this->_query->run("
			SELECT
				sn.notification_id
			FROM
				stock_notification sn
			LEFT JOIN
				product_unit_stock pus USING (unit_id)
			WHERE
				sn.type = 'replenished' AND
				sn.notified_at IS NULL AND
				pus.stock > 0 AND
				pus.location = :location?s
		", array(
			'location' => $this->_stockLocations->getRoleLocation('sell')->name
		));

		return count($result) ? $this->_load($result->flatten(), true) : false;
	}

	public function getNotified()
	{

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

			$entity->user = $this->_userLoader->getByEmail($result[$key]->email);

			$entity->unit = $this->_unitLoader->getByID($result[$key]->unit_id);

			$notifications[$entity->id] = $entity;
		}

		return count($notifications) == 1 && !$alwaysReturnArray ? array_shift($notifications) : $notifications;
	}

}