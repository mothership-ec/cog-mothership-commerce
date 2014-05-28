<?php

namespace Message\Mothership\Commerce\Order\Transaction;

/**
 * Collection of records for a specific transaction.
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class RecordCollection implements \IteratorAggregate, \Countable
{
	protected $_records = array();

	/**
	 * Constructor adding initial records.
	 *
	 * @param array $records An array of records to add
	 */
	public function __construct(array $records = array())
	{
		foreach ($records as $record) {
			$this->add($record);
		}
	}

	/**
	 * Adds a record to the collection.
	 *
	 * @param RecordInterface $record Record to be added
	 */
	public function add(RecordInterface $record)
	{
		if ($this->exists($record)) {
			throw new \InvalidArgumentException(
				sprintf(
					'Record with ID `%s` and type `%s` already exists in this collection.',
					$record->getRecordID(),
					$record->getRecordType()
				)
			);
		}

		$this->_records[] = $record;

		return $this;
	}

	/**
	 * Returns all elements of the collection filtered by type.
	 *
	 * @param  string $type           Type to be searched for
	 *
	 * @return array[RecordInterface] Array of records of type $type
	 */
	public function getByType($type)
	{
		$elements = [];

		foreach ($this->_records as $record) {
			if ($type == $record->getRecordType()) {
				$elements[] = $record;
			}
		}

		return $elements;
	}

	/**
	 * Returns one or none element of the collection by its type.
	 *
	 * @param  string                     $type Transaction type
	 * @throws \InvalidArgumentException        If more than one elements were found.
	 * @return false|RecordInterface            False if no transaction was found, the element
	 *                                          if there was exactly one match.
	 */
	public function getOneByType($type)
	{
		$elements = $this->getByType($type);

		if (count($elements) > 1) {
			throw new \InvalidArgumentException(sprintf(
				'More than one records of type %s exist in this collection. If you want an array of records, use `getByType()` instead.',
				$type
			));
		}

		return reset($elements);
	}

	/**
	 * Get all records on this collection.
	 *
	 * @return array
	 */
	public function all()
	{
		return $this->_records;
	}

	/**
	 * Check whether $record already exists in collection
	 *
	 * @param  RecordInterface $record Record to be checked
	 *
	 * @return boolean                 true if it exists
	 */
	public function exists(RecordInterface $record)
	{
		foreach ($this->_records as $curRecord) {
			if ($curRecord->getRecordType() === $record->getRecordType()
			 && $curRecord->getRecordID() === $record->getRecordID()) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get the number of records registered on this collection.
	 *
	 * @return int The number of records registered
	 */
	public function count()
	{
		return count($this->_records);
	}

	/**
	 * Get the iterator object to use for iterating over this class.
	 *
	 * @return \ArrayIterator An \ArrayIterator instance for the `_records`
	 *                        property
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->_records);
	}
}