<?php

namespace Message\Mothership\Commerce\Order\Entity\Refund;

use Message\Mothership\Commerce\Order\Entity\EntityInterface;
use Message\Mothership\Commerce\Refund as BaseRefund;

use Message\Cog\ValueObject\Authorship;
use Message\Mothership\Commerce\Order\Transaction\RecordInterface;

class Refund implements EntityInterface, RecordInterface
{
	public $order;
	public $refund;

	/**
	 * Constructor.
	 *
	 * @param BaseRefund $refund The base refund for this order entity
	 */
	public function __construct(BaseRefund $refund)
	{
		$this->refund = $refund;
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
		return $this->refund->getRecordType();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRecordID()
	{
		return $this->refund->getRecordID();
	}
}