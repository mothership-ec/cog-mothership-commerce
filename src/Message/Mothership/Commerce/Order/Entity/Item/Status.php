<?php

namespace Message\Mothership\Commerce\Order\Entity\Item;

use Message\Cog\ValueObject\Authorship;

class Status extends \Message\Mothership\Commerce\Order\Status\Status
{
	public $authorship;

	public function __construct($code, $name)
	{
		parent::__construct($code, $name);

		$this->authorship = new Authorship;
	}
}