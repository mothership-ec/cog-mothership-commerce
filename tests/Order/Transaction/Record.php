<?php

namespace Message\Mothership\Commerce\Test\Order\Transaction;

use Message\Mothership\Commerce\Order\Transaction\RecordInterface;

class Record implements RecordInterface
{
	public $id;
	public $type;

	public function __construct($id = 1, $type = 'test')
	{
		$this->id   = $id;
		$this->type = $type;
	}

	public function getRecordType()
	{
		return $this->type;
	}

	public function getID()
	{
		return $this->id;
	}
}
