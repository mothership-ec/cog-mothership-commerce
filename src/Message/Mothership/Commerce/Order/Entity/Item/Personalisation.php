<?php

namespace Message\Mothership\Commerce\Order\Entity\Item;

/**
 * "POPO" representing personalisation data for an order item.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Personalisation
{
	public $senderName;
	public $recipientName;
	public $recipientEmail;
	public $message;

	public function isEmpty()
	{
		return !($this->senderName || $this->recipientName || $this->recipientEmail || $this->message);
	}
}