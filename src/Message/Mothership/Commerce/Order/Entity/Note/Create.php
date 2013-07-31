<?php

namespace Message\Mothership\Commerce\Order\Entity\Note;

use Message\Mothership\Commerce\Order;

use Message\Cog\DB;

/**
 * Order note creator.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Create implements DB\TransactionalInterface
{
	protected $_query;

	public function __construct(DB\Query $query)
	{
		$this->_query = $query;
	}

	public function setTransaction(DB\Transaction $trans)
	{
		$this->_query = $trans;
	}

	public function create(Note $note)
	{
		// Set create authorship data if not already set
		if (!$note->authorship->createdAt()) {
			$note->authorship->create(
				new DateTimeImmutable,
				$this->_currentUser->id
			);
		}

		$this->_query->add('
			INSERT INTO
				order_note
			SET
				order_id          = :orderID?i,
				created_at        = :createdAt?i,
				created_by        = :createdBy?in,
				note              = :note?s,
				customer_notified = :customerNotified?b,
				raised_from       = :raisedFrom?sn
		', array(
			'orderID'          => $note->order->id,
			'createdAt'        => $note->authorship->createdAt(),
			'createdBy'        => $note->authorship->createdBy(),
			'note'             => $note->note,
			'customerNotified' => $note->customerNotified,
			'raisedFrom'       => $note->raisedFrom,
		));

		// use note loader to re-load this item and return it ONLY IF NOT IN ORDER CREATION TRANSACTION
		return $note;
	}
}