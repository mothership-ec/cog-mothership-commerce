<?php

namespace Message\Mothership\Commerce\Refund;

use Message\User\UserInterface;

use Message\Mothership\Commerce\Order;

use Message\Cog\DB;
use Message\Cog\Event\DispatcherInterface;
use Message\Cog\ValueObject\DateTimeImmutable;

use InvalidArgumentException;

/**
 * Refund creator.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Create implements DB\TransactionalInterface
{
	protected $_loader;
	protected $_eventDispatcher;
	protected $_currentUser;

	protected $_trans;
	protected $_transOverridden = false;

	public function __construct(
		DB\Transaction $trans,
		Loader $loader,
		DispatcherInterface $eventDispatcher,
		UserInterface $currentUser
	) {
		$this->_trans           = $trans;
		$this->_loader          = $loader;
		$this->_eventDispatcher = $eventDispatcher;
		$this->_currentUser     = $currentUser;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setTransaction(DB\Transaction $trans)
	{
		$this->_trans           = $trans;
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

		$event = new Event\TransactionalRefundEvent($refund);
		$event->setTransaction($this->_trans);

		$refund = $this->_eventDispatcher->dispatch(
			Events::CREATE_START,
			$event
		)->getRefund();

		$this->_trans->run('
			INSERT INTO
				refund
			SET
				payment_id  = :paymentID?in,
				created_at  = :createdAt?d,
				created_by  = :createdBy?in,
				currency_id = :currencyID?s,
				method      = :method?sn,
				amount      = :amount?f,
				reason      = :reason?sn,
				reference   = :reference?sn
		', array(
			'paymentID'  => $refund->payment ? $refund->payment->id : null,
			'createdAt'  => $refund->authorship->createdAt(),
			'createdBy'  => $refund->authorship->createdBy(),
			'currencyID' => $refund->currencyID,
			'method'     => $refund->method->getName(),
			'amount'     => $refund->amount,
			'reason'     => $refund->reason,
			'reference'  => $refund->reference,
		));

		$sqlVariable = 'REFUND_ID_' . uniqid();

		$this->_trans->setIDVariable($sqlVariable);
		$refund->id = '@' . $sqlVariable;

		if (!$this->_transOverridden) {
			$this->_trans->commit();

			return $this->_loader->getByID($this->_trans->getIDVariable($sqlVariable));
		}

		return $refund;
	}

	protected function _validate(Refund $refund)
	{
		if ($refund->amount <= 0) {
			throw new InvalidArgumentException('Could not create refund: amount must be greater than 0');
		}

		if (!$refund->currencyID) {
			throw new InvalidArgumentException('Could not create refund: currency ID must be set');
		}

		if ($refund->payment && $refund->currencyID !== $refund->payment->currencyID) {
			throw new InvalidArgumentException(sprintf(
				'Could not create refund: currency ID (%s) does not match the related payment\'s currency ID (%s)',
				$refund->currencyID,
				$refund->payment->currencyID
			));
		}
	}
}