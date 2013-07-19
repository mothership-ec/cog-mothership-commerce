<?php

namespace Message\Mothership\Commerce\Order\Entity\Item;

use Message\User\UserInterface;

use Message\Mothership\Commerce\Order;

use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Order item creator.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Create implements Order\Entity\TransactionalDecoratorInterface
{
	protected $_query;

	public function __construct(DB\QueryableInterface $query, UserInterface $currentUser)
	{
		$this->_query = $query;
	}

	public function setTransaction(DB\Transaction $trans)
	{
		$this->_query = $trans;
	}

	public function create(Item $item)
	{
		// Set create authorship data if not already set
		if (!$item->authorship->createdAt()) {
			$item->authorship->create(
				new DateTimeImmutable,
				$this->_currentUser->id
			);
		}

		$this->_query->run('
			INSERT INTO
				order_item
			SET
				order_id          = :orderID?i,
				created_at        = :createdAt?d,
				created_by        = :createdBy?in,
				list_price        = :listPrice?f,
				net               = :net?f,
				discount          = :discount?f,
				tax               = :tax?f,
				tax_rate          = :taxRate?f,
				gross             = :gross?f,
				rrp               = :rrp?fn,
				product_id        = :productID?in,
				product_name      = :productName?sn,
				unit_id           = :unitID?in,
				unit_revision     = :unitRevision?in,
				sku               = :sku?sn,
				barcode           = :barcode?sn,
				options           = :options?sn,
				brand_id          = :brandID?in,
				brand_name        = :brandName?sn,
				weight_grams      = :weight?in,
				stock_location_id = :stockLocation?i
		', array(
			'orderID'       => $item->order->id,
			'createdAt'     => $item->authorship->createdAt(),
			'createdBy'     => $item->authorship->createdBy(),
			'listPrice'     => $item->listPrice,
			'net'           => $item->net,
			'discount'      => $item->discount,
			'tax'           => $item->tax,
			'taxRate'       => $item->taxRate,
			'gross'         => $item->gross,
			'rrp'           => $item->rrp,
			'productID'     => $item->productID,
			'productName'   => $item->productName,
			'unitID'        => $item->unitID,
			'unitRevision'  => $item->unitRevision,
			'sku'           => $item->sku,
			'barcode'       => $item->barcode,
			'options'       => $item->options,
			'brandID'       => $item->brandID,
			'brandName'     => $item->brandName,
			'weight'        => $item->weight,
			'stockLocation' => $item->stockLocation->id,
		));

		// insert personalisation?
		//
		// insert initial status?
		// use item loader to re-load this item and return it ONLY IF NOT IN ORDER CREATION TRANSACTION
		return $item;
	}
}