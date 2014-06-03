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

	public function testVoid()
	{
		$date = new DateTimeImmutable;
		$user = 4;
		$this->transaction->void($date, $user);

		$this->assertEquals($this->transaction->voidedAt, $date);
		$this->assertEquals($this->transaction->voidedBy, $user);

		$this->transaction->void();

		$this->assertTrue($this->transaction->isVoided());
		$this->assertNull($this->transaction->voidedBy);
		$this->assertInstanceOf('\\Message\\Cog\\ValueObject\\DateTimeImmutable', $this->transaction->voidedAt);
	}

	public function testAddRecord()
	{
		$this->assertEquals(count($this->transaction->records), 0);

		$this->transaction->addRecord(new Record(9, 'test1'));

		$this->assertEquals(count($this->transaction->records), 1);
		$this->assertEquals($this->transaction->records[0]->getID(), 9);

		$this->transaction->addRecord(new Record(9, 'test2'));
		$this->assertEquals(count($this->transaction->records), 2);
	}

	/**
	 * @expectedException \InvalidArgumentException
	 * @expectedExceptionMessage The record with ID `1` and record-type `test` has already been added.
	 */
	public function testAddSameRecordTwice()
	{
		$this->transaction->addRecord(new Record(1, 'test'));
		$this->transaction->addRecord(new Record(1, 'test'));
	}

	/**
	 * @expectedException \Exception
	 */
	public function testNonUpdatable()
	{
		$this->transaction->authorship->update(new DateTimeImmutable, 1);
	}

	/**
	 * @expectedException \Exception
	 */
	public function testNonDeletable()
	{
		$this->transaction->authorship->delete(new DateTimeImmutable, 1);
	}
}