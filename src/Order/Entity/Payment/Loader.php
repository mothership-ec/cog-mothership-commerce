<?php

namespace Message\Mothership\Commerce\Order\Entity\Payment;

use Message\Mothership\Commerce\Order;
use Message\Mothership\Commerce\Payment\Loader as BaseLoader;
use Message\Mothership\Commerce\Payment\Payment as BasePayment;

use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Order payment loader.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Loader extends Order\Entity\BaseLoader implements Order\Transaction\RecordLoaderInterface
{
	protected $_query;
	protected $_paymentLoader;
	protected $_includeDeleted = false;

	public function __construct(DB\Query $query, BaseLoader $paymentLoader)
	{
		$this->_query         = $query;
		$this->_paymentLoader = $paymentLoader;
	}

	/**
	 * Toggle whether to load deleted payments.
	 *
	 * @param  bool $bool True to load deleted payments, false otherwise
	 *
	 * @return Loader     Returns $this for chainability
	 */
	public function includeDeleted($bool = true)
	{
		$this->_paymentLoader->includeDeleted((bool) $bool);

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getByOrder(Order\Order $order)
	{
		$result = $this->_query->run('
			SELECT
				payment_id
			FROM
				order_payment
			WHERE
				order_id = ?i
		', $order->id);

		return $this->_load($result->flatten(), true, $order);
	}

	/**
	 * @see \Message\Mothership\Commerce\Payment\Loader::getByMethodAndReference
	 */
	public function getByMethodAndReference($method, $reference)
	{
		$payments = $this->_paymentLoader->getByMethodAndReference($method, $reference);

		if ($payments instanceof BasePayment) {
			return $this->_convertPaymentToOrderEntity($payments);
		}

		if (is_array($payments)) {
			foreach ($payments as $key => $payment) {
				if ($payment instanceof BasePayment) {
					$payments[$key] = $this->_convertPaymentToOrderEntity($payment);
				}
			}

			return $payments;
		}

		return false;
	}

	public function getByID($id, Order\Order $order = null)
	{
		return $this->_load($id, false, $order);
	}

	/**
	 * Alias of getByID() for `Order\Transaction\RecordLoaderInterface`.
	 *
	 * @see getByID
	 *
	 * @param  int $id       The payment ID
	 *
	 * @return Payment|false The payment, or false if it doesn't exist
	 */
	public function getByRecordID($id)
	{
		return $this->getByID($id);
	}

	protected function _load($ids, $alwaysReturnArray = false, Order\Order $order = null)
	{
		$payments = $this->_paymentLoader->getByID($ids);
		$return   = [];

		if (false == $payments || 0 === count($payments)) {
			return $alwaysReturnArray ? [] : false;
		}

		if (!is_array($payments) && $alwaysReturnArray) {
			$payments = [$payments];
		}

		if (!is_array($payments)) {
			return $this->_convertPaymentToOrderEntity($payments, $order);
		}

		foreach ($payments as $payment) {
			$return[$payment->id] = $this->_convertPaymentToOrderEntity($payment, $order);
		}

		return $alwaysReturnArray || count($return) > 1 ? $return : reset($return);
	}

	/**
	 * Convert base `Payment` instances returned by the base payment loader into
	 * order entity payments and set the order parameter on them.
	 *
	 * @param  BasePayment      $payment The base payment to convert
	 * @param  Order\Order|null $order   The related order, if you have it. If
	 *                                   null it will be loaded automagically
	 *
	 * @return Payment                   The converted payment
	 *
	 * @throws \LogicException If the payment to be converted is not linked to
	 *                         any order (and therefore cannot be converted)
	 */
	protected function _convertPaymentToOrderEntity(BasePayment $payment, Order\Order $order = null)
	{
		$return = new Payment($payment);

		// Find the order and load it if it was not passed
		if (!$order) {
			$result = $this->_query->run('
				SELECT
					order_id
				FROM
					order_payment
				WHERE
					payment_id = ?i
			', $payment->id);

			if (count($result) !== 1) {
				throw new \LogicException(sprintf('Payment #%s is not an order payment and cannot be loaded', $payment->id));
			}

			$order = $this->_orderLoader->getByID($result->value());

			if (!$order) {
				throw new \LogicException(sprintf('Order #%s not found', $result->value()));
			}
		}

		$return->order = $order;

		return $return;
	}
}