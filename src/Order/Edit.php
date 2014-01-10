<?php

namespace Message\Mothership\Commerce\Order;

use Message\User\UserInterface;

use Message\Cog\DB;
use Message\Cog\Event\Dispatcher;
use Message\Cog\ValueObject\DateTimeImmutable;

class Edit implements DB\TransactionalInterface
{
	protected $_query;
	protected $_transOverridden = false;

	protected $_eventDispatcher;
	protected $_statuses;
	protected $_currentUser;

	public function __construct(DB\Transaction $query, Dispatcher $eventDispatcher,
		Status\Collection $statuses, UserInterface $currentUser)
	{
		$this->_query           = $query;
		$this->_eventDispatcher = $eventDispatcher;
		$this->_statuses        = $statuses;
		$this->_currentUser     = $currentUser;
	}

	public function setTransaction(DB\Transaction $trans)
	{
		$this->_query           = $trans;
		$this->_transOverridden = true;
	}

	public function updateStatus(Order $order, $statusCode)
	{
		if (!$this->_statuses->exists($statusCode)) {
			throw new \InvalidArgumentException(sprintf('Order status `%s` does not exist', $statusCode));
		}

		$status = $this->_statuses->get($statusCode);

		// Skip if the item is already at this status
		if ($status->code === $order->status->code) {
			return false;
		}

		$order->authorship->update(
			new DateTimeImmutable,
			$this->_currentUser->id
		);

		$this->_query->run('
			UPDATE
				order_summary
			SET
				status_code = :status?i,
				updated_at  = :updatedAt?i,
				updated_by  = :updatedBy?in
			WHERE
				order_id = :id?i
		', array(
			'id'        => $order->id,
			'status'    => $status->code,
			'updatedAt' => $order->authorship->updatedAt(),
			'updatedBy' => $order->authorship->updatedBy(),
		));

		$order->status = clone $status;

		if (!$this->_transOverridden) {
			$this->_query->commit();
		}

		return $this->_eventDispatcher->dispatch(
			Events::EDIT,
			new Event\Event($order)
		)->getOrder();
	}

	public function updateMetadata(Order $order)
	{
		foreach ($order->metadata->getRemovedKeys() as $key) {
			$this->_query->run('
				DELETE FROM
					order_metadata
				WHERE
					`order_id` = :orderID?i
				AND `key`      = :key?s
			', array(
				'orderID' => $order->id,
				'key'     => $key,
			));
		}

		foreach ($order->metadata as $key => $value) {
			$this->_query->run('
				REPLACE INTO
					order_metadata
				SET
					`order_id` = :orderID?i,
					`key`      = :key?s,
					`value`    = :value?sn
			', array(
				'orderID' => $order->id,
				'key'     => $key,
				'value'   => $value,
			));
		}

		if (!$this->_transOverridden) {
			$this->_query->commit();
		}

		return $this->_eventDispatcher->dispatch(
			Events::EDIT,
			new Event\Event($order)
		)->getOrder();
	}
}