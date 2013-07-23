<?php

namespace Message\Mothership\Commerce\Order\Entity\Item\Status;

use Message\Cog\ValueObject\Authorship;

/**
 * Order item status, inherits the default status and adds authorship.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Status extends \Message\Mothership\Commerce\Order\Status\Status
{
	public $authorship;

	/**
	 * {@inheritdoc}
	 */
	public function __construct($code, $name)
	{
		parent::__construct($code, $name);

		$this->authorship = new Authorship;
	}
}
