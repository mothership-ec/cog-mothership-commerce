<?php

namespace Message\Mothership\Commerce\Order\Entity\Discount;

use Message\User\UserInterface;

use Message\Mothership\Commerce\Order;

use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Order discount creator.
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

	public function create(Discount $discount)
	{
		// Set create authorship data if not already set
		if (!$discount->authorship->createdAt()) {
			$discount->authorship->create(
				new DateTimeImmutable,
				$this->_currentUser->id
			);
		}

		$result = $this->_query->run('
			INSERT INTO
				order_discount
			SET
				order_id    = :orderID?i,
				created_at  = :createdAt?d,
				created_by  = :createdBy?in,
				code        = :code?sn,
				amount      = :amount?f,
				percentage  = :percentage?fn,
				name        = :name?sn,
				description = :description?sn
		', array(
			'orderID'     => $discount->order->id,
			'createdAt'   => $discount->authorship->createdAt(),
			'createdBy'   => $discount->authorship->createdBy(),
			'code'        => $discount->code,
			'amount'      => $discount->amount,
			'percentage'  => $discount->percentage,
			'name'        => $discount->name,
			'description' => $discount->description,
		));

		if ($this->_query instanceof DB\Transaction) {
			return $discount;
		}

		return $this->_loader->getByID($result->id(), $discount->order);
	}
}