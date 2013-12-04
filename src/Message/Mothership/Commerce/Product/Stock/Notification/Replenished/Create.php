<?php

namespace Message\Mothership\Commerce\Product\Stock\Notification\Replenished;

class Create {

	protected $_query;

	public function __construct(DB\Query $query)
	{
		$this->_query = $query;
	}

	public function create(Notification $notification)
	{
		$this->_query->run("
			INSERT INTO
				stock_notification
			SET
				`type`       = 'replenished',
				`unit_id`    = :unitID?i,
				`email`      = :email?s,
				`created_at` = :createdAt?d
		", array(
			'unitID'    => $notification->unit->id,
			'email'     => $notification->email,
			'createdAt' => $notification->authorship->createdAt
		));
	}

}