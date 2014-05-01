<?php

namespace Message\Mothership\Commerce\Order\Event;

use Message\Mothership\Commerce\Order\Order;
use Message\Mothership\Commerce\Order\Assembler;

/**
 * Event for a given order assembler: this gives listeners access to the
 * assembler instance.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class AssemblerEvent extends Event
{
	protected $_assembler;

	/**
	 * Constructor.
	 *
	 * @param Assembler $assembler The assembler to live in this event
	 */
	public function __construct(Assembler $assembler)
	{
		$this->_assembler = $assembler;
	}

	/**
	 * Get the assembler.
	 *
	 * @return Assembler
	 */
	public function getAssembler()
	{
		return $this->_assembler;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getOrder()
	{
		return $this->_assembler->getOrder();
	}

	/**
	 * {@inheritDoc}
	 */
	public function setOrder(Order $order)
	{
		$this->_assembler->setOrder($order);
	}
}