<?php

namespace Message\Mothership\Commerce\Order\Entity\Payment;

use Message\Mothership\Commerce\Order\Entity\EntityInterface;
use Message\Mothership\Commerce\Payment\Payment as BasePayment;

use Message\Cog\ValueObject\Authorship;
use Message\Mothership\Commerce\Order\Transaction\RecordInterface;

/**
 * Model defining a payment on an order.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Payment implements EntityInterface, RecordInterface
{
	const RECORD_TYPE = 'order-payment';

	public $order;
	public $payment;

	/**
	 * Constructor.
	 *
	 * @param BasePayment|null $payment The base payment for this order entity,
	 *                                  or null to instantiate an empty one
	 */
	public function __construct(BasePayment $payment = null)
	{
		$this->payment = $payment ?: new BasePayment;
	}

	/**
	 * Magic getter. Proxies property accessing to the embedded actual payment
	 * instance.
	 *
	 * @param  string $var Property name
	 *
	 * @return mixed       The value returned from the payment, or null if not
	 *                     found
	 */
	public function __get($var)
	{
		return (isset($this->payment->{$var})) ? $this->payment->{$var} : null;
	}

	/**
	 * Magic setter. Proxies property setting to the embedded actual payment
	 * instance.
	 *
	 * @param  string $var Property name
	 * @param  mixed  $val Property value
	 */
	public function __set($var, $val)
	{
		$this->payment->{$var} = $val;
	}

	/**
	 * Magic isset checker. Proxies property existance checking to the embedded
	 * actual payment instance.
	 *
	 * @param  string $var Property name
	 *
	 * @return boolean
	 */
	public function __isset($var)
	{
		return isset($this->payment->{$var});
	}

	/**
	 * Magic caller. Proxies method calls to the embedded actual payment
	 * instance.
	 *
	 * @param  string $method Method name to call
	 * @param  array  $args   The arguments for the method
	 *
	 * @return mixed          The return value from calling the method
	 *
	 * @throws \BadMethodCallException If the method does not exist on the base
	 *                                 payment instance
	 */
	public function __call($method, $args)
	{
		if (!method_exists($this->payment, $method)) {
			throw new \BadMethodCallException(sprintf('Method `%s` does not exist on `Payment`', $method));
		}

		return call_user_func_array([$this->payment, $method], $args);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRecordType()
	{
		return self::RECORD_TYPE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRecordID()
	{
		return $this->payment->id;
	}
}