<?php

namespace Message\Mothership\Commerce\Order\Entity\Dispatch;

use Message\User\UserInterface;

use Message\Mothership\Commerce\Order;

use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Order dispatch creator.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Create implements DB\TransactionalInterface
{
	protected $_loader;
	protected $_currentUser;

	protected $_query;
	protected $_transOverridden = false;

	public function __construct(DB\Transaction $query, Loader $loader, UserInterface $currentUser)
	{
		$this->_query       = $query;
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
		$this->_query           = $trans;
		$this->_transOverridden = true;

		return $this;
	}

	public function create(Dispatch $dispatch)
	{
		// Set create authorship data if not already set
		if (!$dispatch->authorship->createdAt()) {
			$dispatch->authorship->create(
				new DateTimeImmutable,
				$this->_currentUser->id
			);
		}

		// If no dispatch weight is already set, set it to the sum of the item weights
		if (!$dispatch->weight) {
			$dispatch->weight = 0;

			foreach ($dispatch->items as $item) {
				$dispatch->weight += $item->weight;
			}
		}

		$this->_validate($dispatch);

		$this->_query->add('
			INSERT INTO
				order_dispatch
			SET
				order_id     = :orderID?i,
				created_at   = :createdAt?d,
				created_by   = :createdBy?in,
				shipped_at   = :shippedAt?dn,
				shipped_by   = :shippedBy?in,
				method       = :method?s,
				code         = :code?sn,
				cost         = :cost?fn,
				weight_grams = :weight?in
		', array(
			'orderID'   => $dispatch->order->id,
			'createdAt' => $dispatch->authorship->createdAt(),
			'createdBy' => $dispatch->authorship->createdBy(),
			'shippedAt' => $dispatch->shippedAt,
			'shippedBy' => $dispatch->shippedBy,
			'method'    => $dispatch->method->getName(),
			'code'      => $dispatch->code,
			'cost'      => $dispatch->cost,
			'weight'    => $dispatch->weight,
		));

		$this->_query->setIDVariable('DISPATCH_ID');
		$dispatch->id = '@DISPATCH_ID';

		// Add the dispatch items
		foreach ($dispatch->items as $item) {
			$this->_query->add('
				INSERT INTO
					order_dispatch_item
				SET
					dispatch_id = :dispatchID?i,
					item_id     = :itemID?i
			', array(
				'dispatchID' => $dispatch->id,
				'itemID'     => $item->id,
			));
		}

		// If the transaction was not overriden, run it & return the re-loaded dispatch
		if (!$this->_transOverridden) {
			$this->_query->commit();

			return $this->_loader->getByID($this->_query->getIDVariable('DISPATCH_ID'), $dispatch->order);
		}

		return $dispatch;
	}

	protected function _validate(Dispatch $dispatch)
	{
		if (!($dispatch->method instanceof MethodInterface)) {
			throw new \InvalidArgumentException('Cannot create dispatch: method is not set or invalid');
		}

		if (count($dispatch->items) < 1) {
			throw new \InvalidArgumentException('Cannot create dispatch: must have at least one item');
		}

		foreach ($dispatch->items as $item) {
			if (!($item instanceof Order\Entity\Item\Item)) {
				throw new \InvalidArgumentException('Cannot create dispatch: item not valid');
			}
		}
	}
}