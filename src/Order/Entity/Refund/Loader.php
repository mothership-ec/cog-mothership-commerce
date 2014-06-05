<?php

namespace Message\Mothership\Commerce\Order\Entity\Refund;

use Message\Mothership\Commerce\Order;
use Message\Mothership\Commerce\Refund\Loader as BaseLoader;
use Message\Mothership\Commerce\Refund\Refund as BaseRefund;

use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Order refund loader.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Loader extends Order\Entity\BaseLoader implements
	Order\Transaction\DeletableRecordLoaderInterface,
	Order\Entity\DeletableLoaderInterface
{
	protected $_query;
	protected $_refundLoader;

	public function __construct(DB\Query $query, BaseLoader $refundLoader)
	{
		$this->_query        = $query;
		$this->_refundLoader = $refundLoader;
	}

	/**
	 * Set whether to load deleted refunds. Also sets include deleted on order loader.
	 * 
	 * @param  bool $bool True to load deleted refunds, false otherwise
	 *
	 * @return Loader     Returns $this for chainability
	 */
	public function includeDeleted($bool = true)
	{
		$this->_refundLoader->includeDeleted((bool) $bool);
		$this->_orderLoader->includeDeleted((bool) $bool);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getByOrder(Order\Order $order)
	{
		$result = $this->_query->run('
			SELECT
				refund_id
			FROM
				order_refund
			WHERE
				order_id = ?i
		', $order->id);

		return $this->_load($result->flatten(), true, $order);
	}

	public function getByID($id, Order\Order $order = null)
	{
		return $this->_load($id, is_array($id), $order);
	}

	public function getByIDs(array $ids)
	{
		return $this->_load($ids, true);
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

	protected function _load($ids, $alwaysReturnArray = false, Order\Order $order = null)
	{
		$refunds = $this->_refundLoader->getByID($ids);
		$return  = [];

		if (false == $refunds || 0 === count($refunds)) {
			return $alwaysReturnArray ? [] : false;
		}

		if (!is_array($refunds) && $alwaysReturnArray) {
			$refunds = [$refunds];
		}

		if ($refunds instanceof BaseRefund) {
			return $this->_convertRefundToOrderEntity($refunds, $order);
		}

		foreach ($refunds as $refund) {
			$return[$refund->id] = $this->_convertRefundToOrderEntity($refund, $order);
		}

		return $alwaysReturnArray || count($return) > 1 ? $return : reset($return);
	}

	/**
	 * Convert base `Refund` instances returned by the base refund loader into
	 * order entity refunds and set the order parameter on them.
	 *
	 * @param  BaseRefund      $refund The base refund to convert
	 * @param  Order\Order|null $order The related order, if you have it. If
	 *                                 null it will be loaded automagically
	 *
	 * @return Refund                  The converted refund
	 *
	 * @throws \LogicException If the refund to be converted is not linked to
	 *                         any order (and therefore cannot be converted)
	 */
	protected function _convertRefundToOrderEntity(BaseRefund $refund, Order\Order $order = null)
	{
		$return = new Refund($refund);

		// Find the order and load it if it was not passed
		if (!$order) {
			$result = $this->_query->run('
				SELECT
					order_id
				FROM
					order_refund
				WHERE
					refund_id = ?i
			', $refund->id);

			if (count($result) !== 1) {
				throw new \LogicException(sprintf('Refund #%s is not an order refund and cannot be loaded', $refund->id));
			}

			$order = $this->_orderLoader->getByID($result->value());

			if (!$order) {
				throw new \LogicException(sprintf('Order #%s not found', $result->value()));
			}
		}

		// Override the payment to an order payment, if there is one
		if ($refund->payment) {
			$paymentLoader   = $this->_orderLoader->getEntityLoader('payments');
			$return->payment = $paymentLoader->getByID($refund->payment->id, $order);
		}

		$return->order = $order;

		return $return;
	}
}