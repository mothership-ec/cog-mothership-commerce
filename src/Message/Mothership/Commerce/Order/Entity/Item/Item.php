<?php

namespace Message\Mothership\Commerce\Order\Entity\Item;

use Message\Cog\ValueObject\Authorship;

/**
 * Represents an item on an order.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Item
{
	public $id;

	public $order;
	public $authorship;
	public $status;

	public $listPrice;
	public $net;
	public $discount;
	public $tax;
	public $taxRate;
	public $gross;
	public $rrp;

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

	public function __construct()
	{
		$this->authorship = new Authorship;
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

	//UPDATE THE STATUS FOR THIS ORDER ITEM
	public function updateStatus($status, $staffID = NULL, $date = NULL) {
		$staffID = !empty($staffID) ? (int) $staffID : NULL;
		$DB = new DBquery;
		if (is_null($date)) {
			$date = 'NOW()';
		} else {
			$date = $DB->escape($date);
		}
		$query = 'REPLACE INTO order_item_status SET '
			   . 'order_id  = ' . $this->orderID . ', '
			   . 'item_id   = ' . $this->itemID . ', '
			   . 'status_id = ' . (int) $status . ', '
			   . 'staff_id  = ' . $DB->null($staffID) . ', '
			   . 'status_datetime = ' . $date;
		$DB->query($query);
		if ($DB->result()) {
			$this->getStatus();
		} else {
			throw new OrderException('Problem updating item status');
		}
	}

	//RELEASE HOLD STATUS
	public function releaseHold() {
		$DB = new DBquery;
		$query = 'DELETE FROM order_item_status WHERE '
			   . 'order_id  = ' . $this->orderID . ' AND '
			   . 'item_id   = ' . $this->itemID . ' AND '
			   . 'status_id = ' . ORDER_STATUS_ON_HOLD;
		$DB->query($query);
		if ($DB->error()) {
			throw new OrderException('Problem removing on hold order status');
		}
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

	public function returnable() {
		if($this->statusID < ORDER_STATUS_SHIPPED || $this->statusID >= ORDER_STATUS_RETURNED) {
			return false;
		}
		return true;
	}

	public function getPrice() {
		return $this->originalPrice ?: $this->price;
	}

	public function isPersonalised()
	{
		return ($this->senderName || $this->recipientName || $this->recipientEmail || $this->recipientMessage);
	}

}