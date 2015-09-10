<?php

namespace Message\Mothership\Commerce\Order;

use Message\User;

use Message\Cog\DB;

use Message\Cog\ValueObject\Authorship;
use Message\Cog\ValueObject\DateTimeImmutable;

use Message\User\UserInterface;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\DB\QueryBuilder;

/**
 * Decorator for loading orders.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Loader implements Transaction\DeletableRecordLoaderInterface
{
	protected $_query;
	protected $_eventDispatcher;
	protected $_userLoader;
	protected $_statuses;
	protected $_itemStatuses;
	protected $_entities;
	protected $_orderBy;
	protected $_includeDeleted = false;

	private $_qbFactory;

	public function __construct(
		DB\Query $query,
		User\Loader $userLoader,
		Status\Collection $statuses,
		Status\Collection $itemStatuses,
		array $entities,
		QueryBuilderFactory $qbFactory

	) {
		$this->_qbFactory    = $qbFactory;
		$this->_query        = $query;
		$this->_userLoader   = $userLoader;
		$this->_statuses     = $statuses;
		$this->_itemStatuses = $itemStatuses;
		$this->_entities     = $entities;
		$this->_prepareEntities();
	}

	public function getEntities()
	{
		$return = array();

		foreach ($this->_entities as $name => $loader) {
			$return[$name] = clone $loader;
		}

		return $return;
	}

	public function count($statuses = null)
	{
		$qb = $this->_qbFactory->getQueryBuilder();

		$qb->select("count(*)")
			->from("order_summary")
		;

		if (is_array($statuses)) {
			$qb->where('status_code IN (?ij)', [$statuses]);
		} else if (is_numeric($statuses)) {
			$status = (int) $statuses;
			$qb->where('status_code = ?i', $status);
		}

		return $qb->getQuery()
			->run()
			->value()
		;
	}

	public function getBySlice($offset, $limit)
	{
		$qb = $this->_qbFactory->getQueryBuilder()
			->select('order_id')
			->from('order_summary')
			->limit($offset, $limit)
		;

		if ($this->_getOrderByStatement()) {
			$qb->orderBy($this->_getOrderByStatement());
		}

		$ids = $qb
			->getQuery()
			->run()
			->flatten()
		;

		return $this->_load($ids, true);
	}

	/**
	 * Get the loader for a specific entity.
	 *
	 * @param  string $name Entity name
	 *
	 * @return Entity\LoaderInterface The entity loader
	 */
	public function getEntityLoader($name)
	{
		if (!array_key_exists($name, $this->_entities)) {
			throw new \InvalidArgumentException(sprintf('Unknown order entity: `%s`', $name));
		}

		$loader = $this->_entities[$name]->getLoader();

		return $loader;
	}

	/**
	 * Set whether to load deleted orders.
	 *
	 * @param  bool $bool    true / false as to whether to include deleted orders
	 *
	 * @return Loader        Loader object in order to chain the methods
	 */
	public function includeDeleted($bool = true)
	{
		$this->_includeDeleted = (bool) $bool;

		return $this;
	}

	/**
	 * Gets whether this loader also loads deleted orders.
	 * 
	 * @return bool Whether the loader loads deleted orders or not.
	 */
	public function getIncludeDeleted()
	{
		return $this->_includeDeleted;
	}

	/**
	 * Get a specific order or orders by ID.
	 *
	 * @param  int|array $id            The order ID, or array of order IDs
	 *
	 * @return Order|array[Order]|false The order, or false if it doesn't exist
	 */
	public function getByID($id)
	{
		if (is_array($id)) {
			return $this->_load($id, true);
		}

		return $this->_load($id);
	}


	/**
	 * Alias of getByID for Transaction\RecordLoaderInterface
	 * @param  int $id record id
	 * @return Order|array[Order]|false The order, or false if it doesn't exist
	 */
	public function getByRecordID($id)
	{
		return $this->getByID($id);
	}


	/**
	 * Get all orders placed by a specific user.
	 *
	 * @param  User $user   The user to get orders for
	 *
	 * @return array[Order] Array of orders
	 */
	public function getByUser(User\User $user)
	{
		$result = $this->_query->run('
			SELECT
				order_id
			FROM
				order_summary
			WHERE
				user_id = ?i
		', $user->id);

		return $this->_load($result->flatten(), true);
	}

	/**
	 * Get orders with specific statuses.
	 *
	 * @param  int|array $statuses Status code or array of status codes
	 *
	 * @return array[Order]        Array of orders
	 *
	 * @throws \InvalidArgumentException If any status codes are not known
	 */
	public function getByStatus($statuses, $limitFrom = 9999, $limitTo = null)
	{
		if ($limitTo === null) {
			$limitTo = $limitFrom;
			$limitFrom = null;
		}

		if (!is_array($statuses)) {
			$statuses = (array) $statuses;
		}

		foreach ($statuses as $code) {
			if (!$this->_statuses->exists($code)) {
				throw new \InvalidArgumentException(sprintf('Unknown order status code: `%s`', $code));
			}
		}
	
		$qb = $this->_qbFactory->getQueryBuilder();

		$result = $qb
			->select('order_id')
			->from('order_summary')
			->where('status_code IN (?ij)', [$statuses])
			->orderBy($this->_getOrderByStatement() ?: 'order_summary.created_at DESC')
		;

		if ($limitFrom) {
			$qb->limit($limitFrom, $limitTo);
		} elseif ($limitTo) {
			$qb->limit($limitTo);
		}

		$result = $qb->getQuery()->run();

		$this->_orderBy = $this->_orderBy ?: OrderOrder::CREATED_DATE;

		return $this->_load($result->flatten(), true);
	}

	/**
	 * Get orders for items with a specific current status.
	 *
	 * At least one item in the order must have one of the given statuses as its
	 * most recent (current) status.
	 *
	 * @param  int|array $statuses Status code or array of status codes
	 *
	 * @return array[Order]        Array of orders
	 *
	 * @throws \InvalidArgumentException If any item status codes are not known
	 */
	public function getByCurrentItemStatus($statuses)
	{
		if (!is_array($statuses)) {
			$statuses = (array) $statuses;
		}

		foreach ($statuses as $code) {
			if (!$this->_itemStatuses->exists($code)) {
				throw new \InvalidArgumentException(sprintf('Unkown order item status code: `%s`', $code));
			}
		}


		// Order by created_at DESC, status_code DESC to solve issue:
		// https://github.com/messagedigital/uniform_wares/issues/320
		// Where pick & pack statuses have exactly the same timestamp when actioned together.
		$result = $this->_query->run('
			SELECT
				order_item.order_id,
				status_code
			FROM
				order_item
			JOIN (
				SELECT
					*
				FROM
					order_item_status
				ORDER BY
					order_item_status.created_at DESC, order_item_status.status_code DESC
			) AS statuses USING (item_id)
			GROUP BY
				item_id
			HAVING
				status_code IN (?ij)
		', array((array) $statuses));

		return $this->_load(array_unique($result->flatten()), true);
	}

	/**
	 * Search for an order based on a given term. The term will be split by spaces, and the following fields will be
	 * searched:
	 *
	 * - Order ID
	 * - User email address
	 * - Customer forename
	 * - Customer surname
	 * - Address lines 1-4
	 * - Address country
	 * - Address postcode
	 * - Address telephone number
	 * - Address town
	 * - Address state (full name, not code)
	 * - Address country (full name, not code)
	 * - Discount code
	 * - Dispatch ID
	 * - Dispatch code
	 * - Order note
	 * - Payment reference
	 *
	 * Results must make at least one match of each word, therefore more words narrows the search further. The order ID
	 * will be searched first and if it matches, that will be the only order that is loaded.
	 *
	 * @param $term
	 * @return array|bool|mixed
	 */
	public function getBySearchTerm($term)
	{
		if (!is_string($term)) {
			throw new \InvalidArgumentException('Search term must be a string');
		}

		if (is_numeric($term)) {
			$order = $this->_load($term, true);

			if (count($order) > 0) {
				return $order;
			}
		}

		$queryBuilder = $this->_qbFactory->getQueryBuilder()
			->select('os.order_id')
			->from('os', 'order_summary')
			->leftJoin('addr', 'addr.order_id = os.order_id', 'order_address')
			->leftJoin('disc', 'disc.order_id = os.order_id', 'order_discount')
			->leftJoin('disp', 'disp.order_id = os.order_id', 'order_dispatch')
			->leftJoin('note', 'note.order_id = os.order_id', 'order_note')
			->leftJoin('order_payment', 'order_payment.payment_id = os.order_id')
			->leftJoin('payment', 'payment.payment_id = order_payment.payment_id')
		;

		$terms = explode(' ', $term);

		array_walk($terms, function (&$term) {
			$term = trim($term);
		});

		$terms = array_unique($terms);

		foreach ($terms as $term) {
			$term = '%' . $term . '%';

			$queryBuilder->where('(
				os.user_email LIKE :term?s OR
				addr.forename LIKE :term?s OR
				addr.surname LIKE :term?s OR
				addr.line_1 LIKE :term?s OR
				addr.line_2 LIKE :term?s OR
				addr.line_3 LIKE :term?s OR
				addr.line_4 LIKE :term?s OR
				addr.country LIKE :term?s OR
				addr.postcode LIKE :term?s OR
				addr.telephone LIKE :term?s OR
				addr.town LIKE :term?s OR
				addr.state LIKE :term?s OR
				disc.code LIKE :term?s OR
				disp.dispatch_id LIKE :term?s OR
				disp.code LIKE :term?s OR
				note.note LIKE :term?s OR
				payment.reference LIKE :term?s
			)', ['term' => $term]);
		}

		$ids = $queryBuilder->run()->flatten();

		return $this->_load($ids, true);

	}

	public function getByTrackingCode($code)
	{
		$result = $this->_query->run('
			SELECT
				os.order_id
			FROM
				order_summary os
			LEFT JOIN
				order_dispatch od ON os.order_id = od.order_id
			WHERE
				od.code = ?s
		', $code);

		return $this->_load($result->flatten(), true);
	}

	/**
	 * Set the order in which the loader should load the orders. Use constants declared within `OrderOrder` class
	 *
	 * @param $orderBy
	 * @throws \InvalidArgumentException
	 *
	 * @return Loader
	 */
	public function orderBy($orderBy)
	{
		if (!is_string($orderBy)) {
			throw new \InvalidArgumentException('Order by statement must be a string constant from the `OrderOrder` class, ' . gettype($orderBy) . ' given');
		}

		$this->_orderBy = $orderBy;

		return $this;
	}

	protected function _load($ids, $returnArray = false)
	{
		$orderBy = $this->_getOrderByStatement();
		$includeDeleted = $this->_includeDeleted ? '' : 'AND deleted_at IS NULL';
		$this->_orderBy = null;

		if (!is_array($ids)) {
			$ids = (array) $ids;
		}

		if (0 === count($ids)) {
			return $returnArray ? array() : false;
		}

		$result = $this->_query->run('
			SELECT
				order_summary.*,
				order_summary.order_id         AS id,
				order_summary.order_id         AS orderID,
				order_summary.deleted_at       AS deletedAt,
				order_summary.deleted_by       AS deletedBy,
				order_summary.user_email       AS userEmail,
				order_summary.currency_id      AS currencyID,
				order_summary.conversion_rate  AS conversionRate,
				order_summary.product_net      AS productNet,
				order_summary.product_discount AS productDiscount,
				order_summary.product_tax      AS productTax,
				order_summary.product_gross    AS productGross,
				order_summary.total_net        AS totalNet,
				order_summary.total_discount   AS totalDiscount,
				order_summary.total_tax        AS totalTax,
				order_summary.total_gross      AS totalGross,
				order_shipping.name            AS shippingName,
				order_shipping.display_name    AS shippingDisplayName,
				order_shipping.list_price      AS shippingListPrice,
				order_shipping.net             AS shippingNet,
				order_shipping.discount        AS shippingDiscount,
				order_shipping.tax             AS shippingTax,
				order_shipping.tax_rate        AS shippingTaxRate,
				order_shipping.gross           AS shippingGross
			FROM
				order_summary
			LEFT JOIN
				order_shipping USING (order_id)
			WHERE
				order_summary.order_id IN (?ij)
			' . $includeDeleted .'
			GROUP BY
				order_summary.order_id
			' . ($orderBy ? 'ORDER BY ' . $orderBy : '') . '
		', array($ids));
		if (0 === count($result)) {
			return $returnArray ? array() : false;
		}

		$self       = $this;
		$userLoader = $this->_userLoader;
		$statuses   = $this->_statuses;
		$query      = $this->_query;

		$orders = $result->bindWith(function($row) use ($self, $userLoader, $statuses, $query)
		{
			$order = new Order($self->getEntities());

			foreach ($row as $k => $v) {
				if (property_exists($order, $k)) {
					$order->$k = $v;
				}
			}

			// Cast integers to integers (for when MySQLnd not installed)
			$order->id      = (int) $row->id;
			$order->orderID = (int) $row->id;

			// Cast decimals to float
			$order->conversionRate    = (float) $row->conversionRate;
			$order->productNet        = (float) $row->productNet;
			$order->productDiscount   = (float) $row->productDiscount;
			$order->productTax        = (float) $row->productTax;
			$order->productGross      = (float) $row->productGross;
			$order->totalNet          = (float) $row->totalNet;
			$order->totalDiscount     = (float) $row->totalDiscount;
			$order->totalTax          = (float) $row->totalTax;
			$order->totalGross        = (float) $row->totalGross;
			$order->shippingListPrice = (float) $row->shippingListPrice;
			$order->shippingNet       = (float) $row->shippingNet;
			$order->shippingDiscount  = (float) $row->shippingDiscount;
			$order->shippingTax       = (float) $row->shippingTax;
			$order->shippingTaxRate   = (float) $row->shippingTaxRate;
			$order->shippingGross     = (float) $row->shippingGross;

			$order->taxable = (bool) $row->taxable;

			$order->user = $userLoader->getByID($row->user_id);

			$order->authorship->create(
				new DateTimeImmutable(date('c', $row->created_at)),
				$row->created_by
			);

			if ($row->deleted_at) {
				$order->authorship->delete(
					new DateTimeImmutable(date('c', $row->deleted_at)),
					$row->deleted_by
				);
			}

			if ($row->updated_at) {
				$order->authorship->update(
					new DateTimeImmutable(date('c', $row->updated_at)),
					$row->updated_by
				);
			}

			$order->status = $statuses->get($row->status_code);

			$result = $query->run('
				SELECT
					`key`,
					`value`
				FROM
					order_metadata
				WHERE
					order_id = ?i
			', $order->id);

			foreach ($result->hash('key', 'value') as $key => $value) {
				$order->metadata->set($key, $value);
			}

			$shippingTaxes = $query->run(
				"SELECT * FROM 
					`order_shipping_tax` 
				WHERE
					`order_id` = '$order->id'"
			);

			$rates = [];
			foreach ($shippingTaxes as $rate) {
				$rates[$rate->tax_type] = $rate->tax_rate;
			}
			$order->setShippingTaxes($rates);

			return $order;
		});

		return $returnArray ? $orders : reset($orders);
	}

	/**
	 * Return ORDER BY statement for queries based on what `$this->_orderBy` is set to
	 *
	 * @return null|string
	 */
	private function _getOrderByStatement()
	{
		switch ($this->_orderBy) {
			case OrderOrder::ID :
				return 'order_summary.order_id ASC';
			case OrderOrder::ID_REVERSE :
				return 'order_summary.order_id DESC';
			case OrderOrder::UPDATED_DATE :
				return 'order_summary.updated_at ASC';
			case OrderOrder::UPDATED_DATE_REVERSE :
				return 'order_summary.updated_at DESC';
			case OrderOrder::CREATED_DATE :
				return 'order_summary.created_at ASC';
			case OrderOrder::CREATED_DATE_REVERSE :
				return 'order_summary.created_at DESC';
			case OrderOrder::NONE :
				return null;
			default :
				return null;
		}
	}

	/**
	 * Prepares entities by setting their loaders' order loader to $this.
	 * This is necessary to make sure the entity loaders will always have an
	 * order loader.
	 */
	protected function _prepareEntities()
	{
		foreach ($this->_entities as $entity) {
			$loader = $entity->getLoader();
			$loader->setOrderLoader($this);
		}
	}
}