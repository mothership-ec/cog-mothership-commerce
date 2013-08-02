<?php

namespace Message\Mothership\Commerce\Order\Entity\Dispatch;

use Message\User\UserInterface;

use Message\Cog\DB;
use Message\Cog\Event\DispatcherInterface;
use Message\Cog\ValueObject\DateTimeImmutable;

class Edit implements DB\TransactionalInterface
{
	protected $_query;
	protected $_currentUser;

	public function __construct(DB\QueryableInterface $query, UserInterface $currentUser)
	{
		$this->_query       = $query;
		$this->_currentUser = $currentUser;
	}

	public function setTransaction(DB\Transaction $trans)
	{
		$this->_query = $trans;
	}

	public function ship(Dispatch $dispatch)
	{

	}

	public function update(Dispatch $dispatch)
	{

	}
}