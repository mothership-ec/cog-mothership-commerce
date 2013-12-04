<?php

namespace Message\Mothership\Commerce\Product\Stock\Notification\Replenished;

use Message\Cog\ValueObject\Authorship;

class Notification {

	public $id;
	public $type;
	public $email;
	public $authorship;
	public $notifiedAt;

	public function __construct()
	{
		$this->authorship = new Authorship;

		$this->authorship->disableUpdate();
		$this->authorship->disableDelete();
	}

}