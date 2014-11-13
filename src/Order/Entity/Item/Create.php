<?php

namespace Message\Mothership\Commerce\Order\Entity\Item;

use Message\Mothership\Commerce\Order;
use Message\Mothership\Commerce\Product\Stock;

use Message\User\UserInterface;

use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Cog\Event\DispatcherInterface;

/**
 * Order item creator.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Create implements DB\TransactionalInterface
{
	protected $_allowedTaxStrategies = array(
		'inclusive',
		'exclusive',
	);

	protected $_query;
	protected $_transOverridden = false;

	protected $_loader;
	protected $_eventDispatcher;
	protected $_currentUser;

	public function __construct(DB\Transaction $query, Loader $loader, DispatcherInterface $eventDispatcher,
		UserInterface $currentUser)
	{
		$this->_query           = $query;
		$this->_loader          = $loader;
		$this->_eventDispatcher = $eventDispatcher;
		$this->_currentUser     = $currentUser;
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

	public function create(Item $item)
	{
		$event = new Order\Event\EntityEvent($item->order, $item);
		$event->setTransaction($this->_query);

		$item = $this->_eventDispatcher->dispatch(
			Order\Events::ENTITY_CREATE,
			$event
		)->getEntity();

		// Set create authorship data if not already set
		if (!$item->authorship->createdAt()) {
			$item->authorship->create(
				new DateTimeImmutable,
				$this->_currentUser->id
			);
		}

		$this->_validate($item);

		$this->_query->add('
			INSERT INTO
				order_item
			SET
				order_id          = :orderID?i,
				created_at        = :createdAt?d,
				created_by        = :createdBy?in,
				list_price        = :listPrice?f,
				actual_price      = :actualPrice?f,
				base_price        = :basePrice?f,
				net               = :net?f,
				discount          = :discount?f,
				tax               = :tax?f,
				tax_rate          = :taxRate?f,
				product_tax_rate  = :productTaxRate?f,
				gross             = :gross?f,
				rrp               = :rrp?fn,
				tax_strategy      = :taxStrategy?sn,
				product_id        = :productID?in,
				product_name      = :productName?sn,
				unit_id           = :unitID?in,
				unit_revision     = :unitRevision?in,
				sku               = :sku?sn,
				barcode           = :barcode?sn,
				options           = :options?sn,
				brand             = :brand?sn,
				weight_grams      = :weight?in,
				stock_location    = :stockLocation?s
		', array(
			'orderID'        => $item->order->id,
			'createdAt'      => $item->authorship->createdAt(),
			'createdBy'      => $item->authorship->createdBy(),
			'listPrice'      => $item->listPrice,
			'actualPrice'    => $item->actualPrice,
			'basePrice'      => $item->basePrice,
			'net'            => $item->net,
			'discount'       => $item->discount,
			'tax'            => $item->tax,
			'taxRate'        => $item->taxRate,
			'productTaxRate' => $item->productTaxRate,
			'gross'          => $item->gross,
			'rrp'            => $item->rrp,
			'taxStrategy'    => $item->taxStrategy,
			'productID'      => $item->productID,
			'productName'    => $item->productName,
			'unitID'         => $item->unitID,
			'unitRevision'   => $item->unitRevision,
			'sku'            => $item->sku,
			'barcode'        => $item->barcode,
			'options'        => $item->options,
			'brand'          => $item->brand,
			'weight'         => $item->weight,
			'stockLocation'  => $item->stockLocation->name,
		));

		$sqlVariable = 'ITEM_ID_' . uniqid();

		$this->_query->setIDVariable($sqlVariable);
		$item->id = '@' . $sqlVariable;

		// Set the initial status, if defined
		if ($item->status) {
			if (!$item->status->authorship->createdAt()) {
				$item->status->authorship->create(
					$item->authorship->createdAt(),
					$item->authorship->createdBy()
				);
			}

			$this->_query->add('
				INSERT INTO
					order_item_status
				SET
					order_id    = :orderID?i,
					item_id     = :itemID?i,
					status_code = :code?i,
					created_at  = :createdAt?d,
					created_by  = :createdBy?in
			', array(
				'orderID'   => $item->order->id,
				'itemID'    => $item->id,
				'code'      => $item->status->code,
				'createdAt' => $item->status->authorship->createdAt(),
				'createdBy' => $item->status->authorship->createdBy(),
			));
		}

		$event = new Order\Event\EntityEvent($item->order, $item);
		$event->setTransaction($this->_query);

		$item = $this->_eventDispatcher->dispatch(
			Events::CREATE_PRE_PERSONALISATION_INSERTS,
			$event
		)->getEntity();

		// Set personalisation data, if defined
		foreach ($item->personalisation as $name => $value) {
			$this->_query->add('
				INSERT INTO
					order_item_personalisation
				SET
					item_id = :itemID?i,
					name    = :name?s,
					value   = :value?sn
			', array(
				'itemID' => $item->id,
				'name'   => $name,
				'value'  => $value,
			));
		}

		// Insert item tax rates
		$tokens  = [];
		$inserts = [];

		foreach ($item->getTaxRates() as $type => $rate) {
			$tokens[] = '(?i, ?s, ?f)';
			
			$inserts[] = $item->id;
			$inserts[] = $type;
			$inserts[] = $rate;
		}

		$this->_query->add(
			"INSERT INTO 
				`order_item_tax` (`item_id`, `tax_type`, `tax_rate`) 
			VALUES " . implode(',', $tokens),
			$inserts
		);

		// If the query was not in a transaction, return the re-loaded item
		if (!$this->_transOverridden) {
			$this->_query->commit();

			return $this->_loader->getByID($this->_query->getIDVariable($sqlVariable), $item->order);
		}

		return $item;
	}

	protected function _validate(Item $item)
	{
		if ($item->personalisation && !($item->personalisation instanceof Personalisation)) {
			throw new \InvalidArgumentException('Item personalisation must be an instance of `Personalisation`');
		}

		if (!($item->stockLocation instanceof Stock\Location\Location)) {
			throw new \InvalidArgumentException('Item must have a valid stock location');
		}

		if (!in_array($item->taxStrategy, $this->_allowedTaxStrategies)) {
			throw new \InvalidArgumentException(sprintf(
				'Item must have a valid tax strategy (one of `%s`). `%s` given.',
				implode('`, `', $this->_allowedTaxStrategies),
				$item->taxStrategy
			));
		}
	}
}