<?php

namespace Message\Mothership\Commerce\Order\Entity\Refund;

use Message\Mothership\Commerce\Order\Entity\EntityInterface;
use Message\Mothership\Commerce\Refund\Refund as BaseRefund;

use Message\Cog\ValueObject\Authorship;
use Message\Mothership\Commerce\Order\Transaction\RecordInterface;

class Refund implements EntityInterface, RecordInterface
{
	const RECORD_TYPE = 'order-refund';

	public $order;
	public $refund;

	/**
	 * Constructor.
	 *
	 * @param BaseRefund|null $refund The base refund for this order entity,
	 *                                or null to instantiate an empty one
	 */
	public function __construct(BaseRefund $refund = null)
	{
		$this->refund = $refund ?: new BaseRefund;
	}

	/**
	 * Magic getter. Proxies property accessing to the embedded actual refund
	 * instance.
	 *
	 * @param  string $var Property name
	 *
	 * @return mixed       The value returned from the refund, or null if not
	 *                     found
	 */
	public function __get($var)
	{
		return (isset($this->refund->{$var})) ? $this->refund->{$var} : null;
	}

	/**
	 * Magic setter. Proxies property setting to the embedded actual refund
	 * instance.
	 *
	 * @param  string $var Property name
	 * @param  mixed  $val Property value
	 */
	public function __set($var, $val)
	{
		$this->refund->{$var} = $val;
	}

	/**
	 * Magic isset checker. Proxies property existance checking to the embedded
	 * ctual refund instance.
	 *
	 * @param  string $var Property name
	 *
	 * @return boolean
	 */
	public function __isset($var)
	{
		return isset($this->refund->{$var});
	}

	/**
	 * Magic caller. Proxies method calls to the embedded actual refund
	 * instance.
	 *
	 * @param  string $method Method name to call
	 * @param  array  $args   The arguments for the method
	 *
	 * @return mixed          The return value from calling the method
	 *
	 * @throws \BadMethodCallException If the method does not exist on the base
	 *                                 refund instance
	 */
	public function __call($method, $args)
	{
		if (!method_exists($this->refund, $method)) {
			throw new \BadMethodCallException(sprintf('Method `%s` does not exist on `Refund`', $method));
		}

		return call_user_func_array([$this->refund, $method], $args);
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
		return $this->refund->id;
	}
}