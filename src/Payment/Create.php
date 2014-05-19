<?php

namespace Message\Mothership\Commerce\Payment;

use Message\Mothership\Commerce\Order;

use Message\User\UserInterface;

use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Payment creator.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 * @author Iris Schaffer <iris@message.co.uk>
 */
class Create implements DB\TransactionalInterface
{
	protected $_loader;
	protected $_currentUser;

	protected $_trans;
	protected $_transOverridden = false;

	public function __construct(DB\Transaction $query, Loader $loader, UserInterface $currentUser)
	{
		$this->_trans       = $query;
		$this->_loader      = $loader;
		$this->_currentUser = $currentUser;
	}

	/**
	 * Sets transaction and sets $_transOverridden to true.
	 *
	 * @param  DB\Transaction $trans transaction
	 * @return Create                $this for chainability
	 */
	public function setTransaction(DB\Transaction $trans)
	{
		$this->_trans           = $trans;
		$this->_transOverridden = true;

		return $this;
	}

	/**
	 * Creates a payment.
	 *
	 * @param  Payment $payment Payment to be created
	 *
	 * @return Payment          The created payment, reloaded if the transaction
	 *                          was not overridden
	 */
	public function create(Payment $payment)
	{
		// Set create authorship data if not already set
		if (!$payment->authorship->createdAt()) {
			$payment->authorship->create(
				new DateTimeImmutable,
				$this->_currentUser->id
			);
		}

		$result = $this->_trans->run('
			INSERT INTO
				payment
			SET
				created_at = :createdAt?d,
				created_by = :createdBy?in,
				method     = :method?sn,
				amount     = :amount?f,
				`change`   = :change?fn,
				reference  = :reference?sn
		', array(
			'createdAt'   => $payment->authorship->createdAt(),
			'createdBy'   => $payment->authorship->createdBy(),
			'method'      => $payment->method->getName(),
			'amount'      => $payment->amount,
			'change'      => $payment->change,
			'reference'   => $payment->reference,
		));

		$sqlVariable = 'PAYMENT_ID_' . spl_object_hash($payment);

		$this->_trans->setIDVariable($sqlVariable);
		$payment->id = '@' . $sqlVariable;

		if (!$this->_transOverridden) {
			$this->_trans->commit();

			return $this->_loader->getByID($this->_trans->getIDVariable($sqlVariable));
		}

		return $payment;
	}
}