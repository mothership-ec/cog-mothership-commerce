<?php

namespace Message\Mothership\Commerce\Product\Stock\Notification\Replenished;

use DateTime;

class Edit {

	protected $_query;

	public function __construct(DB\Query $query)
	{
		$this->_query = $query;
	}

	public function setNotified(Notification $notification)
	{
		$notification->notifiedAt = new DateTime();

		$this->_query->run("
			UPDATE
				stock_notification
			SET
				notified_at = :notifiedAt?d
			WHERE
				notification_id = :id?i
		", array(
			'notifiedAt' => $notification->notifiedAt,
			'id'         => $notification->id
		));
	}

}