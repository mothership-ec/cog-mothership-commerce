<?php

namespace Message\Mothership\Commerce\Refund;

use Message\Mothership\Commerce\Order;
use Message\Mothership\Commerce\Order\Entity\Payment\MethodCollection;
use Message\Mothership\Commerce\Order\Entity\Payment\Loader as PaymentLoader;

use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Refund loader.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Loader implements Order\Transaction\RecordLoaderInterface
{
	protected $_query;
	protected $_methods;
	protected $_paymentLoader;

	public function __construct(DB\Query $query, MethodCollection $paymentMethods/*, PaymentLoader $paymentLoader*/)
	{
		$this->_query         = $query;
		$this->_methods       = $paymentMethods;
		// $this->_paymentLoader = $paymentLoader;
	}

	public function getByID($id)
	{
		return $this->_load($id, false);
	}

	/**
	 * Alias of getByID() for `Order\Transaction\RecordLoaderInterface`.
	 *
	 * @see getByID
	 *
	 * @param  int $id      The refund ID
	 *
	 * @return Refund|false The refund, or false if it doesn't exist
	 */
	public function getByRecordID($id)
	{
		return $this->getByID($id);
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
				refund_id AS id
			FROM
				refund
			WHERE
				refund_id IN (?ij)
		', array($ids));

		if (0 === count($result)) {
			return $alwaysReturnArray ? array() : false;
		}

		$entities = $result->bindTo('Message\\Mothership\\Commerce\\Refund\\Refund');
		$return   = array();

		foreach ($result as $key => $row) {
			// Cast decimals to float
			$entities[$key]->amount = (float) $row->amount;

			$entities[$key]->authorship->create(
				new DateTimeImmutable(date('c', $row->created_at)),
				$row->created_by
			);

			if ($row->payment_id) {
				// $entities[$key]->payment = $this->_paymentLoader->getByID($row->payment_id);
			}

			$entities[$key]->method = $this->_methods->get($row->method);

			$return[$row->id] = $entities[$key];
		}

		return $alwaysReturnArray || count($return) > 1 ? $return : reset($return);
	}
}