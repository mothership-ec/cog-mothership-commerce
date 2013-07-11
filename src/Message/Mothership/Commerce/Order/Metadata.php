<?php

namespace Message\Mothership\Commerce\Order;

class Metadata
{
	protected $_metadata;
	protected $_orderID;


	public function __construct($orderID) {
		$this->_orderID = (is_int($orderID) ? $orderID : NULL);
		$this->_loadMetadata();
	}


	public function __destruct() {
		$this->_updateMetadata();
	}


	public function __get($key) {
		return (isset($this->_metadata->{$key}) ? $this->_metadata->{$key} : NULL);
	}


	public function __toString() {
		$str = '';
		foreach ($this->_metadata as $key => $val) {
			$str .= $key . ' = ' . $val . '; ';
		}
		return $str;
	}


	public function set($key, $value) {
		$this->_metadata->{ (string) $key } = $value;
		if (is_null($this->_metadata->{ (string) $key })) {
			$this->delete($key);
		}
	}


	public function delete($key) {
		unset($this->_metadata->{ (string) $key });
	}


	public function save() {
		$this->_updateMetadata();
	}


	public function setOrderID($orderID) {
		$this->_orderID = $orderID;
	}


	public function _updateMetadata() {
		if ($this->_orderID && count($this->_metadata)) {
			$db = new DBtransaction;
			$inserts = array();
			foreach ($this->_metadata as $key => $val) {
				$inserts[] = '(' . $this->_orderID . ',' . $db->escape($key) . ',' . $db->escape($val) . ')';
			}
			$db->add('DELETE FROM order_metadata '
				   . 'WHERE order_id = ' . $this->_orderID);
			if ($inserts) {
				$db->add('REPLACE INTO order_metadata (order_id, metadata_key, metadata_value) '
					   . 'VALUES ' . implode(', ', $inserts) );
			}
			$db->run();
			$result = $db->result();
			unset($db);
			return $result;
		}
	}


	protected function _loadMetadata() {
		$this->_metadata = (object) array();
		if ($this->_orderID) {
			$query = 'SELECT metadata_key, metadata_value '
				   . 'FROM order_metadata '
				   . 'WHERE order_id = ' . $this->_orderID;
			$db = new DBquery($query);
			if ($db->result()) {
				foreach ($db->rows('OBJECT') as $row) {
					$this->_metadata->{$row->metadata_key} = $row->metadata_value;
				}
			}
			unset($db);
		}
	}



}


?>