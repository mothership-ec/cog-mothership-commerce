<?php

namespace Message\Mothership\Commerce\Order\EventListener;

use Message\Mothership\Commerce\Order\Event;
use Message\Mothership\Commerce\Order\Status;
use Message\Mothership\Commerce\Order\Statuses;

use Message\Cog\Event\SubscriberInterface;

/**
 * Order event listener for updating the overall status.
 *
 * @todo set the status depending on the item statuses (listeners when editing)
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class StatusListener implements SubscriberInterface
{
	protected $_statuses;

	/**
	 * {@inheritdoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(Event::CREATE_START => array(
			array('setDefaultStatus'),
		));
	}

	public function __construct(Status\Collection $statuses)
	{
		$this->_statuses = $statuses;
	}

	/**
	 * If no status has been set on the order yet, set it to the default status
	 * (awaiting dispatch).
	 *
	 * @param Event $event The event object
	 */
	public function setDefaultStatus(Event $event)
	{
		if (!$event->getOrder()->status) {
			$event->getOrder()->status = $this->_statuses->get(Statuses::AWAITING_DISPATCH);
		}
	}
}