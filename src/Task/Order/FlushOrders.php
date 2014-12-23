<?php

namespace Message\Mothership\Commerce\Task\Order;

use Message\Cog\Console\Task\Task;

class FlushOrders extends Task
{
	public function process()
	{
		$orderIDQuery = $this->get('db.query.builder.factory')->getQueryBuilder()
			->select('order_id')
			->from('order_summary')
			->where('order_summary.status_code IN (?ji)', [[500,800]])
		;

		$itemStatuses = $this->get('db.query.builder.factory')->getQueryBuilder()
			->select(['order_item_status.order_id, item_id, status_code'])
			->from('order_item_status')
			->join('order_ids', 'order_ids.order_id = order_item_status.order_id', $orderIDQuery)
			->getQuery()
			->run()
		;

		$data = [];
		$completed = [];

		foreach ($itemStatuses as $status) {
			if (!array_key_exists($status->order_id, $data)) {
				$data[$status->order_id] = [];
			}
			if (!array_key_exists($status->item_id, $data[$status->order_id])) {
				$data[$status->order_id][$status->item_id] = [];
			}
			$data[$status->order_id][$status->item_id][] = $status->status_code;
		}

		foreach ($data as $orderID => $items) {
			$orderComplete = true;
			foreach ($items as $statuses) {
				$itemComplete = false;
				foreach ($statuses as $code) {
					if ($code >= 1000) {
						$itemComplete = true;
						continue;
					}
				}
				if (false === $itemComplete) {
					$orderComplete = false;
					continue;
				}
			}
			if ($orderComplete) {
				$this->writeln('Order ' . $orderID . ' complete');
				$completed[] = $orderID;
			} else {
				$this->writeln('<info>Order ' . $orderID . ' not complete</info>');
			}
		}

		$this->get('db.query')->run("
			UPDATE
				order_summary
			SET
				status_code = :statusCode?i
			WHERE
				order_id IN (:orderIDs?ji)
		", [
			'statusCode' => 1000,
			'orderIDs'   => $completed,
		]);
	}
}