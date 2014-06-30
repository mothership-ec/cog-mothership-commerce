<?php

namespace Message\Mothership\Commerce\Product\Stock\Notification\Replenished;

use Message\Cog\DB\Query;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Loader for stock replenished notifications.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
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

	/**
	 * Get all notifications that have not yet been sent to the user.
	 *
	 * @return array[Notification]
	 */
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

	/**
	 * Get all notifications that have not yet been sent to the user and where
	 * the related unit has available stock in the `sell` role.
	 *
	 * @return array[Notification]
	 */
	public function getPending()
	{
		$locations = $this->_stockLocations;

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
			'location' => $locations->getRoleLocation($locations::SELL_ROLE)->name
		));

		return count($result) ? $this->_load($result->flatten(), true) : false;
	}

	/**
	 * Load notifications from a list of ids.
	 *
	 * @param  int|array  $ids
	 * @param  boolean    $alwaysReturnArray
	 * @return array[Notification]
	 */
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

		$this->_unitLoader->includeInvisible(true);
		$this->_unitLoader->includeOutOfStock(true);

		foreach ($entities as $key => $entity) {
			$entity->id = $result[$key]->notification_id;

			// Add created authorship
			$entity->authorship->create(
				new DateTimeImmutable(date('c', $result[$key]->created_at)),
				$result[$key]->created_by
			);

			if ($result[$key]->created_by) {
				$entity->user = $this->_userLoader->getByID($result[$key]->created_by);
			}

			// Load the related unit.
			$entity->unit = $this->_unitLoader->getByID($result[$key]->unit_id);

			$notifications[$entity->id] = $entity;
		}

		return count($notifications) == 1 && !$alwaysReturnArray ? array_shift($notifications) : $notifications;
	}

}