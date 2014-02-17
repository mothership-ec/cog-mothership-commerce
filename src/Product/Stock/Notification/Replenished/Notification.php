<?php

namespace Message\Mothership\Commerce\Product\Stock\Notification\Replenished;

use Message\Cog\ValueObject\Authorship;

/**
 * Stock replenished notification entity.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class Notification
{

	public $id;
	public $type;
	public $email;
	public $user;
	public $unit;
	public $authorship;
	public $notifiedAt;

	public function __construct()
	{
		$this->authorship = new Authorship;

		$this->authorship->disableUpdate();
		$this->authorship->disableDelete();
	}

}