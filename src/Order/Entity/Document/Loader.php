<?php

namespace Message\Mothership\Commerce\Order\Entity\Document;

use Message\Mothership\Commerce\Order;

use Message\Cog\DB;
use Message\Cog\Filesystem\File;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Order document loader.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Loader extends Order\Entity\BaseLoader
{
	protected $_query;

	public function __construct(DB\Query $query)
	{
		$this->_query = $query;
	}

	public function getByID($id, Order\Order $order = null)
	{
		return $this->_load($id, false, $order);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getByOrder(Order\Order $order)
	{
		$result = $this->_query->run('
			SELECT
				document_id
			FROM
				order_document
			WHERE
				order_id = ?i
		', $order->id);

		return $this->_load($result->flatten(), true, $order);
	}

	protected function _load($ids, $alwaysReturnArray = false, Order\Order $order = null)
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
				document_id AS id
			FROM
				order_document
			WHERE
				document_id IN (?ij)
		', array($ids));

		if (0 === count($result)) {
			return $alwaysReturnArray ? array() : false;
		}

		$entities = $result->bindTo('Message\\Mothership\\Commerce\\Order\\Entity\\Document\\Document');
		$return   = array();

		foreach ($result as $key => $row) {
			$entities[$key]->authorship->create(
				new DateTimeImmutable(date('c', $row->created_at)),
				$row->created_by
			);

			$entities[$key]->file = new File($row->url);

			if (!$order || $row->order_id != $order->id) {
				$order = $this->_orderLoader->getByID($row->order_id);
			}

			$entities[$key]->order = $order;

			if ($row->dispatch_id) {
				$entities[$key]->dispatch = $entities[$key]->order->dispatches->get($row->dispatch_id);
			}

			$return[$row->id] = $entities[$key];
		}

		return $alwaysReturnArray || count($return) > 1 ? $return : reset($return);
	}

}