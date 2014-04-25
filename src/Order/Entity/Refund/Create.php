<?php

namespace Message\Mothership\Commerce\Order\Entity\Refund;

use Message\User\UserInterface;

use Message\Mothership\Commerce\Order;

use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Cog\Event\DispatcherInterface;

/**
 * Order refund creator.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Create implements DB\TransactionalInterface
{
	protected $_query;
	protected $_loader;
	protected $_eventDispatcher;
	protected $_currentUser;
	protected $_transOverridden = false;

	public function __construct(
		DB\Transaction $query,
		Loader $loader,
		DispatcherInterface $eventDispatcher,
		UserInterface $currentUser
	) {
		$this->_query           = $query;
		$this->_loader          = $loader;
		$this->_eventDispatcher = $eventDispatcher;
		$this->_currentUser     = $currentUser;
	}

	/**
	 * Sets transaction and sets $_transOverrriden to true.
	 * 
	 * @param  DBTransaction $trans transaction
	 * @return Create               $this for chainability
	 */
	public function setTransaction(DB\Transaction $trans)
	{
		$this->_query = $trans;
		$this->_transOverridden = true;

		return $this;
	}

	public function create(Refund $refund)
	{
		// Set create authorship data if not already set
		if (!$refund->authorship->createdAt()) {
			$refund->authorship->create(
				new DateTimeImmutable,
				$this->_currentUser->id
			);
		}

		$this->_validate($refund);

		$this->_query->run('
			INSERT INTO
				order_refund
			SET
				order_id   = :orderID?i,
				payment_id = :paymentID?in,
				return_id  = :returnID?in,
				created_at = :createdAt?d,
				created_by = :createdBy?in,
				method     = :method?sn,
				amount     = :amount?f,
				reason     = :reason?sn,
				reference  = :reference?sn
		', array(
			'orderID'     => $refund->order->id,
			'paymentID'   => $refund->payment ? $refund->payment->id : null,
			'returnID'    => $refund->return ? $refund->return->id : null,
			'createdAt'   => $refund->authorship->createdAt(),
			'createdBy'   => $refund->authorship->createdBy(),
			'method'      => $refund->method->getName(),
			'amount'      => $refund->amount,
			'reason'      => $refund->reason,
			'reference'   => $refund->reference,
		));

		$sqlVariable = 'REFUND_ID_' . spl_object_hash($refund);

		$this->_query->setIDVariable($sqlVariable);
		$refund->id = '@' . $sqlVariable;

		$event = new Order\Event\EntityEvent($refund->order, $refund);
		$event->setTransaction($this->_query);

		$refund = $this->_eventDispatcher->dispatch(
			Order\Events::ENTITY_CREATE_END,
			$event
		)->getEntity();

		if (!$this->_transOverridden) {
			$this->_query->commit();

			return $this->_loader->getByID($this->_query->getIDVariable($sqlVariable), $refund->order);
		}

		return $refund;
	}

	protected function _validate(Refund $refund)
	{
		if (! $refund->order) {
			throw new \InvalidArgumentException('Could not create refund: no order specified');
		}

		if ($refund->amount <= 0) {
			throw new \InvalidArgumentException('Could not create refund: amount must be greater than 0');
		}
	}
}