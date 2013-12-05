<?php

namespace Message\Mothership\Commerce\Product\Stock\Notification\Replenished;

use Message\Cog\DB\Query;
use Message\Cog\ValueObject\DateTimeImmutable;

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
				// $this->_user->id
			);
		}

		$this->_query->run("
			INSERT INTO
				stock_notification
			SET
				`type`       = 'replenished',
				`unit_id`    = :unitID?i,
				`email`      = :email?s,
				`created_at` = :createdAt?d
		", array(
			'unitID'    => $notification->unitID,
			'email'     => $notification->email,
			'createdAt' => $notification->authorship->createdAt()
		));
	}

}