<?php

namespace Message\Mothership\Commerce\Order\Entity\Document;

use Message\Mothership\Commerce\Order\Entity\EntityInterface;

use Message\Cog\ValueObject\Authorship;

/**
 * Order document entity.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Document implements EntityInterface
{
	public $id;
	public $type;
	public $file;

	public $authorship;

	public $order;
	public $dispatch;

	public function __construct()
	{
		$this->authorship = new Authorship;
	}
}