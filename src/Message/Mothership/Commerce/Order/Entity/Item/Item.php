<?php

namespace Message\Mothership\Commerce\Order\Entity\Item;

use Message\Mothership\Commerce\Order\Entity\EntityInterface;
use Message\Mothership\Commerce\Product\Unit\Unit;

use Message\Cog\ValueObject\Authorship;

/**
 * Represents an item on an order.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Item implements EntityInterface
{
	public $id;

	public $order;
	public $authorship;
	public $status;

	public $listPrice = 0;
	public $net       = 0;
	public $discount  = 0;
	public $tax       = 0;
	public $taxRate   = 0;
	public $gross     = 0;
	public $rrp       = 0;

	public $productID;
	public $productName;
	public $unitID;
	public $unitRevision;
	public $sku;
	public $barcode;
	public $options;
	public $brandID;
	public $brandName;

	public $weight;
	public $stockLocation;

	public $personalisation = array(

	);

	// PERSONALISATION STUFF
	protected $senderName;
	protected $recipientName;
	protected $recipientEmail;
	protected $recipientMessage;

	static public function createFromUnit(Unit $unit)
	{
		$item = new static;
		$item->listPrice = $unit->getPrice('retail')->getPrice($this->order->currencyID);
		$item->rrp = $unit->getPrice('rrp')->getPrice($this->order->currencyID);
		$item->taxRate = $unit->product->taxRate;
		// net, discount, tax, taxRate, gross
		$item->productID = $unit->product->id;
		$item->productName = $unit->product->name;
		$item->unitID = $unit->id;
		$item->unitRevision = $unit->revisionID;
		$item->sku = $unit->sku;
		$item->barcode = $unit->barcode;
		$item->options = ''; // combine all options as a string
		$item->brandID = $unit->product->brandID;
		$item->brandName = ''; // TODO: add this once Brand class used
		$item->weight = $unit->weight;
		// TODO: figure out how tax and tax discounts should work with countries, and WHEN? what about checkout

		return $item;
	}

	public function __construct()
	{
		$this->authorship = new Authorship;

		// TODO: remove the below when stock stuff is built
		$this->stockLocation = (object) array('id' => 1);
	}

	public function price($price)
	{
		$this->price = $price;
		if (!$this->originalPrice) {
			$this->originalPrice = $price;
		}
	}

	public function discount($amount)
	{
		$this->discount = round($amount, 2);
		if (!$this->itemID) {
			$this->calculateTax();
		}
	}

	//CALCULATE THE CORRECT AMOUNT OF TAX FOR THIS ORDER
	public function calculateTax()
	{
		$this->gross = round($item->listPrice - $item->discount, 2);
		$this->tax   = round(($item->gross / (100 + $item->taxRate)) * $item->taxRate, 2);
		$this->net   = round($item->gross - $item->tax, 2);
		if ($this->taxable) {
			$this->tax = (($this->getPrice() - $this->discount) / (100 + $this->taxRate)) * $this->taxRate;
		} else {
			$this->price = round(($this->originalPrice - $this->discount) / (1 + ($this->taxRate / 100)) + $this->discount, 2);
		}
	}

	/**
	 * Get the tax discount amount.
	 *
	 * If tax was charged for this item, `null` is always returned. Otherwise,
	 * the list price minus the discount minus the net amount is returned. This
	 * should equal the tax amount they would have paid if the order was taxable
	 * (that was therefore discounted).
	 *
	 * @return float|null The tax discount amount, or null if there was no tax
	 *                    discount
	 */
	public function getTaxDiscount()
	{
		if ($this->tax) {
			return null;
		}

		return round($this->listPrice - $this->discount - $this->net, 2);
	}

	//FILTER OUT INCREMENTAL STATUS NAMES LEAVING ORDERED, AWAITING DESPATCH, SHIPPED
	public function shortStatus() {
		$status = 'Ordered';
		if ($this->statusID === ORDER_STATUS_PENDING) {
			$status = 'Pending full payment';
		}
		if ($this->statusID > 0) {
			$status = 'Awaiting shipping';
		}
		if ($this->statusID > 5) {
			$status = ucfirst($this->statusName);
		}
		return $status;
	}

	public function getPrice() {
		return $this->originalPrice ?: $this->price;
	}

	public function isPersonalised()
	{
		return ($this->senderName || $this->recipientName || $this->recipientEmail || $this->recipientMessage);
	}

}