<?php

namespace Message\Mothership\Commerce\Test\Order\Transaction;

use Message\Mothership\Commerce\Order\Transaction\Transaction;
use Message\Cog\ValueObject\DateTimeImmutable;


class TransactionTest extends \PHPUnit_Framework_TestCase
{
	public $transaction;

	public function setUp()
	{
		$this->transaction = new Transaction;
	}

	public function testIsVoided()
	{
		$this->assertFalse($this->transaction->isVoided());
		$this->transaction->voidedAt = new DateTimeImmutable;

		$this->assertTrue($this->transaction->isVoided());
	}

	/**
	 * @expectedException \Exception
	 */
	public function testNonDeletable()
	{
		$this->transaction->authorship->delete(new DateTimeImmutable, 1);
	}
}