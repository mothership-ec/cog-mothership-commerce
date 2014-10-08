<?php

namespace Message\Mothership\Commerce\Order\Entity\Dispatch;

use Message\User\UserInterface;

use Message\Cog\DB;
use Message\Cog\Event\DispatcherInterface;
use Message\Cog\ValueObject\DateTimeImmutable;

use Message\Mothership\Commerce\Order;

/**
 * Class for updating dispatch objects in the database.
 */
class Edit implements DB\TransactionalInterface
{
	protected $_trans;
	protected $_currentUser;
	protected $_eventDispatcher;
	protected $_transOverridden;

	public function __construct(DB\Transaction $trans, UserInterface $currentUser, DispatcherInterface $eventDispatcher)
	{
		$this->_trans           = $trans;
		$this->_currentUser     = $currentUser;
		$this->_eventDispatcher = $eventDispatcher;
	}

	/**
	 * Sets transaction and sets _transOverridden to true.
	 * 
	 * @param  DB\Transaction $trans Transaction to be used for editing dispatch
	 * @return Edit                  $this for chainability.
	 */
	public function setTransaction(DB\Transaction $trans)
	{
		$this->_trans = $trans;
		$this->_transOverridden = true;

		return $this;
	}

	/**
	 * Sets shipped metadata on dispatch object and updates object in the database.
	 * Order\Events::DISPATCH_SHIPPED event is fired as soon as transaction is
	 * committed.
	 * 
	 * @param  Dispatch $dispatch Dispatch to be set as shipped
	 * @return Dispatch           The updated dispatch
	 */
	public function ship(Dispatch $dispatch)
	{
		if ($dispatch->shippedAt || $dispatch->shippedBy) {
			throw new \InvalidArgumentException(sprintf('Dispatch #%s cannot be shipped: it is already shipped', $dispatch->id));
		}

		$this->_setUpdatedAuthorship($dispatch);

		$dispatch->shippedAt = new DateTimeImmutable;
		$dispatch->shippedBy = $this->_currentUser->id;

		$this->_trans->run('
			UPDATE
				order_dispatch
			SET
				shipped_at = :shippedAt?d,
				shipped_by = :shippedBy?in
			WHERE
				dispatch_id = :id?i
		', array(
			'shippedAt' => $dispatch->shippedAt,
			'shippedBy' => $dispatch->shippedBy,
			'id'        => $dispatch->id,
		));

		// Attach event to be fired as soon as query is committed
		$this->_trans->attachEvent(
			Order\Events::DISPATCH_SHIPPED,
			function ($transaction) use ($dispatch) {
				return new Order\Event\DispatchEvent($dispatch);
			}
		);

		if (!$this->_transOverridden) {
			$this->_trans->commit();
		}

		return $dispatch;
	}

	/**
	 * Adds postage code, cost and address on a dispatch and updates it in the
	 * database. 
	 * 
	 * @param  Dispatch $dispatch Dispatch to be updated.
	 * @param  string     $code     Dispatch code
	 * @param  float|null $cost     Dispatch cost
	 *
	 * @throws \InvalidArgumentException If dispatch already has a code set
	 * @throws \InvalidArgumentException If dispatch already has a cost set
	 * 
	 * @return Dispatch             The updated dispatch
	 */
	public function postage(Dispatch $dispatch, $code, $cost = null)
	{
		if ($dispatch->code) {
			throw new \InvalidArgumentException(sprintf('Dispatch #%s cannot be postaged: it already has a code', $dispatch->id));
		}

		if ($dispatch->cost) {
			throw new \InvalidArgumentException(sprintf('Dispatch #%s cannot be postaged: it already has a cost', $dispatch->id));
		}

		$dispatch->code = $code;

		$this->_setUpdatedAuthorship($dispatch);

		if (!is_null($cost)) {
			$dispatch->cost = (float) $cost;
		}

		$this->_trans->run('
			UPDATE
				order_dispatch
			SET
				code       = :code?s,
				cost       = :cost?fn,
				address_id = :addressID?i
			WHERE
				dispatch_id = :id?i
		', array(
			'code'       => $dispatch->code,
			'cost'       => $dispatch->cost,
			'addressID'  => $dispatch->order->getAddress('delivery')->id,
			'id'         => $dispatch->id,
		));

		if (!$this->_transOverridden) {
			$this->_trans->commit();
		}

		return $dispatch;
	}

	/**
	 * Updates dispatch and its items in the database.
	 *
	 * @param  Dispatch                  $dispatch Dispatch to be updated
	 *
	 * @throws \InvalidArgumentException           If dispatch has no items set.
	 * 
	 * @return Dispatch                            Updated dispatch
	 */
	public function update(Dispatch $dispatch)
	{
		$this->_setUpdatedAuthorship($dispatch);

		$this->_trans->add('
			UPDATE
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
			WHERE
				dispatch_id = :dispatchID?i
		', [
			'orderID'    => $dispatch->order->id,
			'createdAt'  => $dispatch->authorship->createdAt(),
			'createdBy'  => $dispatch->authorship->createdBy(),
			'shippedAt'  => $dispatch->shippedAt,
			'shippedBy'  => $dispatch->shippedBy,
			'method'     => $dispatch->method->getName(),
			'code'       => $dispatch->code,
			'cost'       => $dispatch->cost,
			'weight'     => $dispatch->weight,
			'dispatchID' => $dispatch->id,
		]);

		$this->_trans->add('
			DELETE FROM
				order_dispatch_item
			WHERE
				dispatch_id = :dispatchID?i
		', [
			'dispatchID' => $dispatch->id,	
		]);

		foreach ($dispatch->items as $item) {
			$this->_trans->add('
				INSERT INTO
					order_dispatch_item
				SET
					dispatch_id = :dispatchID?i,
					item_id     = :itemID?i
			', [
				'dispatchID' => $dispatch->id,
				'itemID'     => $item->id,
			]);
		}

		if (!$this->_transOverridden) {
			$this->_trans->commit();
		}

		return $dispatch;
	}

	/**
	 * Sets updated authorship on dispatch object.
	 * 
	 * @param Dispatch $dispatch Dispatch
	 */
	protected function _setUpdatedAuthorship(Dispatch $dispatch)
	{
		$dispatch->authorship->update(
			new DateTimeImmutable,
			$this->_currentUser->id
		);
	} 
}