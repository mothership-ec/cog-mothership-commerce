<?php

namespace Message\Mothership\Commerce\Product\Stock\Notification\Replenished;

use Message\Cog\DB\Query;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Create decorator for stock replenished notifications.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class Create
{

	protected $_query;

	public function __construct(Query $query)
	{
		$this->_query = $query;
	}

	public function create(Notification $notification)
	{
		// Set create authorship data if not already set
		if (! $notification->authorship->createdAt()) {
			$notification->authorship->create(
				new DateTimeImmutable
			);
		}

		$this->_query->run("
			DELETE FROM
				stock_notification
			WHERE
				`type`    = 'replenished'
			AND	`unit_id` = :unitID?i
			AND	`email`   = :email?s
		", [
			'unitID' => $notification->unitID,
			'email'  => $notification->email,
		]);

		$this->_query->run("
			INSERT INTO
				stock_notification
			SET
				`type`       = 'replenished',
				`unit_id`    = :unitID?i,
				`email`      = :email?s,
				`created_at` = :createdAt?d,
				`created_by` = :createdBy?i
		", array(
			'unitID'    => $notification->unitID,
			'email'     => $notification->email,
			'createdAt' => $notification->authorship->createdAt(),
			'createdBy' => $notification->authorship->createdBy()
		));
	}

}