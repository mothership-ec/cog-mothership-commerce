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

	public function includeVoided()
	{
		$this->_includeVoided = true;

		return $this;
	}

	public function excludeVoided()
	{
		$this->_includeVoided = false;

		return $this;
	}

	public function addRecordLoader($type, RecordLoaderInterface $loader)
	{
		$this->_recordLoaders[$type] = $loader;

		return $this;
	}

	public function setRecordLoaders(array $loaders)
	{
		foreach($loaders as $type => $loader) {
			$this->addRecordLoader($type, $loader);
		}
	}

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


	public function getByID($id)
	{
		return $this->_load($id, false);
	}

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
			$records[$row->record_id] = $loader->getByID($row->record_id);
		}

		return $records;
	}

	protected function _loadAttributes(Transaction $transaction)
	{
		$result = $this->_query->run('
			SELECT
				attribute_name,
				attribute_value
			FROM
				transaction_attribute
			WHERE
				transaction_id = ?i
		', $transaction->id);

		 return $result->hash('attribute_name', 'attribute_value');
	}

	protected function _getLoader($type)
	{
		if(!isset($this->_recordLoaders[$type])) {
			throw new \Exception(sprintf('Cannot load records, because no loader is defined for records of type %s.', $type));
		}

		return $this->_recordLoaders[$type];
	}

}