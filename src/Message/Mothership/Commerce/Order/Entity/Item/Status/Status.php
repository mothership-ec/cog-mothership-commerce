<?php

namespace Message\Mothership\Commerce\Order\Entity\Item\Status;

use Message\Cog\ValueObject\Authorship;

class Status
{
	const ORDERED = 100;

	public $item;
	public $authorship;

	public $status;

	public function __construct()
	{
		$this->authorship = new Authorship;
	}
}