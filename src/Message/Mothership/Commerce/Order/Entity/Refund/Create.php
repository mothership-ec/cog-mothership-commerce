<?php

namespace Message\Mothership\Commerce\Order\Entity\Refund;

use InvalidArgumentException;
use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\User\UserInterface;

/**
 * Order refund creator.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class Create implements DB\TransactionalInterface
{
	protected $_currentUser;
	protected $_query;

	public function __construct(DB\Transaction $query, UserInterface $currentUser)
	{
		$this->_query       = $query;
		$this->_currentUser = $currentUser;
	}

	public function setTransaction(DB\Transaction $trans)
	{
		$this->_query = $trans;
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

		$this->_query->add('
			INSERT INTO
				order_refund
			SET
				order_id = ?i,
				payment_id = ?in,
				return_id = ?in,
				created_at = ?i,
				created_by = ?in,
				method = ?s,
				amount = ?f,
				reason = ?sn,
				reference = ?sn
		', array(
			$refund->order->id,
			($refund->payment) ? $refund->payment->id : null,
			($refund->return) ? $refund->return->id : null,
			$refund->authorship->createdAt(),
			$refund->authorship->createdBy(),
			$refund->method,
			$refund->amount,
			$refund->reason,
			$refund->reference
		));

		$this->_query->commit();

		return $refund;
	}

	protected function _validate(Refund $refund)
	{
		if (! $refund->order) {
			throw new InvalidArgumentException('Could not create refund: no order specified');
		}

		if ($refund->amount <= 0) {
			throw new InvalidArgumentException('Could not create refund: amount must be greater than 0');
		}
	}

}