<?php

namespace Message\Mothership\Commerce\Order\Transaction;

use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Transaction loader
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class Loader
{
	protected $_query;
	protected $_recordLoaders;
	protected $_includeVoided = false;

	public function __construct(DB\Query $query, array $loaders = array())
	{
		$this->_query   = $query;
		$this->setRecordLoaders($loaders);
	}

	/**
	 * Sets $_includeVoided to true, which will result in the loaded transactions
	 * to include voided ones.
	 *
	 * @return $this for chainability
	 */
	public function includeVoided()
	{
		$this->_includeVoided = true;

		return $this;
	}

	/**
	 * Sets $_includeVoided to false, which will result in the loaded transactions
	 * to not include voided transactions.
	 * @return $this for chainability
	 */
	public function excludeVoided()
	{
		$this->_includeVoided = false;

		return $this;
	}

	/**
	 * Adds record loader class to $_recordLoaders.
	 * @param string                $type   Type of record loaded by $loader
	 * @param RecordLoaderInterface $loader Loader which loads records of type
	 *                                      $type
	 */
	public function addRecordLoader($type, RecordLoaderInterface $loader)
	{
		$this->_recordLoaders[$type] = $loader;

		return $this;
	}

	/**
	 * Resets $_recordLoaders and replaces them by the $loaders.
	 * Uses addRecordLoader() to add the loaders in $loaders.
	 * @param array[RecordLoaderInterface] $loaders array of loaders
	 */
	public function setRecordLoaders(array $loaders)
	{
		$this->_recordLoaders = [];
		foreach($loaders as $type => $loader) {
			$this->addRecordLoader($type, $loader);
		}
	}

	/**
	 * Returns all transactions
	 * @return array[Transaction]
	 */
	public function getAll()
	{
		$result = $this->_query->run('
			SELECT
				transaction_id
			FROM
				transaction
		');

		return $this->_load($result->flatten(), true);
	}

	/**
	 * Returns all transaction of type $type
	 * @param  string $type       Type of transactions that should be returned
	 * @return array[Transaction] All transactions of type $type
	 */
	public function getByRecordType($type)
	{
		$result = $this->_query->run('
			SELECT
				transaction_id
			FROM
				transaction
			WHERE
				type = ?s
		', $type);

		return $this->_load($result->flatten(), true);
	}

	/**
	 * Returns Transaction(s) with id(s) $id
	 * @param  int|array[int] $id id(s)
	 * @return Transaction|array[Transaction]|false  transaction(s) or false, if
	 *                                               id(s) are not found
	 */
	public function getByID($id)
	{
		return $this->_load($id, false);
	}

	/**
	 * Does the actual loading of Transactions using their ids
	 * @param  int|arrray[int]  $ids                id(s) used for fetching data
	 * @param  boolean          $alwaysReturnArray  whether result should always
	 *                                              be array
	 * @return array[Transaction]|Transaction|false resulting transactions or false
	 *                                              if none were found
	 */
	protected function _load($ids, $alwaysReturnArray = false)
	{
		if (!is_array($ids)) {
			$ids = (array) $ids;
		}

		if (!$ids) {
			return $alwaysReturnArray ? array() : false;
		}

		$result = $this->_query->run('
			SELECT
				*,
				transaction_id AS id
			FROM
				transaction
			WHERE
				transaction_id IN (?ij)
			' . ($this->_includeVoided ? '' : 'AND voided_at is NULL')
			, array($ids));

		if (0 === count($result)) {
			return $alwaysReturnArray ? array() : false;
		}

		$entities = $result->bindTo('Message\\Mothership\\Commerce\\Order\\Transaction\\Transaction');
		$return   = array();

		foreach ($result as $key => $row) {
			$entities[$key]->authorship->create(
				new DateTimeImmutable(date('c', $row->created_at)),
				$row->created_by
			);

			$entities[$key]->records    = $this->_loadRecords($entities[$key]);
			$entities[$key]->attributes = $this->_loadAttributes($entities[$key]);

			$return[$row->id] = $entities[$key];
		}

		return $alwaysReturnArray || count($return) > 1 ? $return : reset($return);
	}

	/**
	 * Loads all records for a given transaction
	 * @param  Transaction $transaction transaction to get records for
	 * @return array[Transaction]       All records for $transaction
	 */
	protected function _loadRecords(Transaction $transaction)
	{
		$results = $this->_query->run('
			SELECT
				*
			FROM
				transaction_record
			WHERE
				transaction_id = ?i

		', array($transaction->id));

		if(count($results) === 0) {
			return array();
		}

		$records = array();
		foreach($results as $key => $row) {
			$loader = $this->_getLoader($row->type);
			$records[$row->record_id] = $loader->getByRecordID($row->record_id);
		}

		return $records;
	}

	/**
	 * Loads all attributes for a given transaction
	 * @param  Transaction $transaction transaction to get attributes for
	 * @return array[Transaction]       Array of key-value pairs
	 */
	protected function _loadAttributes(Transaction $transaction)
	{
		$result = $this->_query->run('
			SELECT
				name,
				value
			FROM
				transaction_attribute
			WHERE
				transaction_id = ?i
		', $transaction->id);

		 return $result->hash('attribute_name', 'attribute_value');
	}

	/**
	 * Gets the loader for a given $type
	 * @param  string                $type Type loader should be returned for
	 * @return RecordLoaderInterface       Loader for $type
	 * @throws \LogicException             if no loader exists for $type
	 */
	protected function _getLoader($type)
	{
		if(!isset($this->_recordLoaders[$type])) {
			throw new \LogicException(sprintf('Cannot load records, because no loader is defined for records of type %s.', $type));
		}

		return $this->_recordLoaders[$type];
	}

}