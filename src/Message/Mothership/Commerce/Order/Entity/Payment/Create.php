<?php

namespace Message\Mothership\Commerce\Order\Entity\Payment;

use Message\User\UserInterface;

use Message\Mothership\Commerce\Order;

use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Order payment creator.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Create implements DB\TransactionalInterface
{
	protected $_query;
	protected $_loader;
	protected $_currentUser;

	public function __construct(DB\Query $query, Loader $loader, UserInterface $currentUser)
	{
		$this->_query       = $query;
		$this->_loader      = $loader;
		$this->_currentUser = $currentUser;
	}

	public function setTransaction(DB\Transaction $trans)
	{
		$this->_query = $trans;
	}

	public function create(Payment $payment)
	{
		// Set create authorship data if not already set
		if (!$payment->authorship->createdAt()) {
			$payment->authorship->create(
				new DateTimeImmutable,
				$this->_currentUser->id
			);
		}

		$this->_query->add('
			INSERT INTO
				order_payment
			SET
				order_id   = :orderID?i,
				return_id  = :returnID?in,
				created_at = :createdAt?d,
				created_by = :createdBy?in,
				method     = :method?sn,
				amount     = :amount?f,
				reference  = :reference?sn
		', array(
			'orderID'     => $payment->order->id,
			'returnID'    => $payment->return ? $payment->return->id : null,
			'createdAt'   => $payment->authorship->createdAt(),
			'createdBy'   => $payment->authorship->createdBy(),
			'method'      => $payment->method->getName(),
			'amount'      => $payment->amount,
			'reference'   => $payment->reference,
		));

		// If the query was not in a transaction, return the re-loaded payment
		if (!($this->_query instanceof DB\Transaction)) {
			return $this->_loader->getByID($result->id(), $payment->order);
		}

		return $payment;
	}
}