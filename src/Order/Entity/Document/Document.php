<?php

namespace Message\Mothership\Commerce\Order\Entity\Document;

use Message\Mothership\Commerce\Order\Entity\EntityInterface;

use Message\Cog\Filesystem\File;
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

	protected $_filePath;

	public function __construct()
	{
		$this->authorship = new Authorship;

		$this->authorship
			->disableUpdate()
			->disableDelete();
	}

	public function __sleep()
	{
		$this->_filePath = $this->file->getRealPath();

		return [
			'id',
			'type',
			'authorship',
			'order',
			'dispatch',
			'_filePath',
		];
	}

	public function __wakeup()
	{
		$this->file = new File($this->_filePath);
	}
}