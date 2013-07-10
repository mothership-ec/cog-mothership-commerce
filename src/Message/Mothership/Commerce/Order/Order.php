<?php

namespace Message\Mothership\Commerce\Order;

use Message\Cog\Service\Container;

/**
 * Order model. Container for all information about an order.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Order
{
	public $id;
	public $orderID; // alias of $id for BC
	public $userID;
	public $userName;
	public $placedTimestamp;
	public $updateTimestamp;

	public $total;
	public $tax;
	public $taxDiscount;
	public $discount;
	public $paid;
	public $change;
	public $currencyID;
	public $currencySymbol;
	public $shippingID;
	public $shippingName;
	public $shippingAmount;
	public $shippingTax;

	public $items;
	public $addresses;
	public $discounts;
	public $payments;
	public $refunds;
	public $despatches;
	public $returns;
	public $repairs;
	public $notes;

	protected $metadata;

	protected $localeData;

	/**
	 * Constructor.
	 *
	 * For now, this will load the order if an order ID is passed as the first
	 * property using the `Order\Loader` decorator.
	 *
	 * In future, this should be removed once we are confident it doesn't break
	 * BC in favour of always asking `Order\Loader` directly for orders.
	 *
	 * @todo Don't call the service container statically: dependency inject an
	 *       array of collections instead (where the key is the entity name: this
	 *       will allow us to add custom entities in other Cogules)
	 * @todo Don't call the order loader here, once all references to this
	 *       shortcut are removed.
	 *
	 * @param int|null $orderID The ID of the order to load, or null to not load
	 */
	public function __construct($orderID = null)
	{
		$this->items      = new Entity\Collection($this, Container::get('order.item.loader'));
		// $this->addresses  = new Entity\Collection($this, Container::get('order.address.loader'));
		// $this->discounts  = new Entity\Collection($this, Container::get('order.discounts.loader'));
		// $this->payments   = new Entity\Collection($this, Container::get('order.payment.loader'));
		// $this->refunds    = new Entity\Collection($this, Container::get('order.refund.loader'));
		// $this->despatches = new Entity\Collection($this, Container::get('order.despatch.loader'));
		// $this->returns    = new Entity\Collection($this, Container::get('order.return.loader'));
		// $this->repairs    = new Entity\Collection($this, Container::get('order.repair.loader'));
		// $this->notes      = new Entity\Collection($this, Container::get('order.note.loader'));
		// order.item.loader is an instance of Order\Loader\Item which implements CollectionLoader ???

		if ($orderID) {
			// statically call loader.. but wait, how the fuck can we replace $this?
			// maybe we need to pass it $this and it sets all the properties? man that's lame.
		}

	}

	public function getItemArray()
	{
		// TODO: dunno what's best here. some sort of niceness?
		return $this->getItems(); // pass whatever propetty we use for "rolling up quantities"
	}


	/**
	 * Get the items for this order.
	 *
	 * @param  mixed $filter DEPRECATED "filter", only "SKIP_RETURNS", an item
	 *                       ID or null can be passed
	 *
	 * @return Collection    Collection of the order items
	 */
	public function getItems($filter = NULL)
	{
		// Backwards-compatibility with pre-mothership code
		if ('SKIP_RETURNS' === $filter) {
			return $this->getNonReturnedItems();
		}

		// Backwards-compatibility with pre-mothership code
		if (is_int($filter) || ctype_digit($filter)) {
			return $this->items->get($filter);
		}

		return $this->items;
	}

	/**
	 * Get items for this order that do not have returns raised against them.
	 *
	 * @return Collection Collection of order items that don't have a return
	 *                    raised against them
	 */
	public function getNonReturnedItems()
	{
		$items = clone $this->items;

		foreach ($items as $id => $item) {
			// TODO: check if the item is returned here
			if (0) {
				$items->remove($key);
			}
		}

		return $items;
	}

	//RETURN THE ADDRESS ITEMS
	public function getAddress($type)
	{
		$this->addresses->load();
		foreach ($this->addresses->getItems() as $address) {
			$class = 'OrderAddress' . ucfirst(strtolower($type));
			if ($address instanceof $class) {
				return $address;
			}
		}
		return false;
	}


	//GET THE COUNTRY ID FOR THIS ORDER
	public function getCountryID($addressType) {
		if ($address = $this->getAddress($addressType)) {
			return $address->countryID;
		}
		elseif($this->shopID) {
			$DB = new DBquery;
			$query = "SELECT country_id FROM shop JOIN lkp_address_country USING (address_id) WHERE shop_id = ".$this->shopID;
			if ($DB->query($query)) {
				return $DB->value();
			}
		}
		return NULL;
	}

	//RETURN THE DISCOUNT ITEMS
	public function getDiscounts() {
		$this->discounts->load();
		return $this->discounts->getItems();
	}


	//RETURN THE CAMPAIGN ITEMS
	public function getCampaigns() {
		$this->campaigns->load();
		return $this->campaigns->getItems();
	}


	//RETURN THE DESPATCH ITEMS
	public function getDespatches($despatchID = NULL) {
		$this->despatches->load();
		if ($despatchID) {
			foreach ($this->getDespatches() as $despatch) {
				if ($despatch->despatchID == $despatchID) {
					return $despatch;
				}
			}
			return NULL;
		}
		return $this->despatches->getItems();
	}


	//RETURN THE PAYMENT ITEMS
	public function getPayments($id = NULL) {
		$this->payments->load();
		if ($id) {
			foreach ($this->payments->getItems() as $payment) {
				if ($payment->paymentID == $id) {
					return $payment;
				}
			}
			return NULL;
		}
		return $this->payments->getItems();
	}


	//RETURN THE PAYMENT ITEMS
	public function getRefunds($id = NULL) {
		$this->refunds->load();
		if ($id) {
			foreach ($this->refunds->getItems() as $refund) {
				if ($refund->refundID == $id) {
					return $refund;
				}
			}
			return NULL;
		}
		return $this->refunds->getItems();
	}


	//RETURN THE PAYMENT ITEMS
	public function getReturns($id = NULL) {
		$this->returns->load();
		if ($id) {
			foreach ($this->returns->getItems() as $return) {
				if ($return->returnID == $id) {
					return $return;
				}
			}
			return NULL;
		}
		return $this->returns->getItems();
	}


	//RETURN AN ARRAY OF [ONLY] ACTIVE RETURNS
	public function getReturnsByStatus($status) {
		$returns = array();
		switch ($status) {
			case 'INCOMPLETE':
				foreach ($this->getReturns() as $return) {
					if ($return->statusID < RETURN_STATUS_COMPLETE) {
						$returns[] = $return;
					}
				}
				break;
			default:
				$returns[] = $return;
		}
		return $returns;
	}



	//RETURN THE RECEIPTS
	public function getReceipts() {
		$this->receipts->load();
		return $this->receipts->getItems();
	}

	//RETURN THE REPAIRS
	public function getRepairs() {
		$this->repairs->load();
		return $this->repairs->getItems();
	}


	//DOES THE ORDER INCLUDE FREE SHIPPING?
	public function hasFreeShipping() {
		foreach ($this->getDiscounts() as $item) {
			if ($item instanceof OrderDiscountFreeShipping) {
				return true;
			}
		}
		return false;
	}

	//RETURN THE TOTAL DISCOUNT ON THIS ORDER
	//IF A TYPE IS PASSED IN, EITHER RETURN THE DISCOUNT VALUE FOR THIS TYPE
	//OR EXCLUDE THIS TYPE FROM THE TOTAL, DEPENDENT ON $include
	public function getDiscount($type = NULL, $exclude = false) {
		$discountVal  = $this->discount;
		$typeDiscount = 0;
		if ($type) {
			$class = 'OrderDiscount' . $type;
			foreach ($this->getDiscounts() as $discount) {
				if (get_class($discount) == $class) {
					$typeDiscount += $discount->amount;
				}
			}
			if ($exclude) {
				$discountVal -= $typeDiscount;
			} else {
				$discountVal = $typeDiscount;
			}
		}
		return $discountVal;
	}


	public function getSubtotal() {
		return $this->total;
	}


	//GET TOTAL FOR THIS ORDER
	public function getTotal() {
		return ($this->total + $this->shippingAmount) - $this->discount - $this->taxDiscount;
	}


	//GET THE UNPAID BALANCE ON THE ORDER
	public function amountDue($onlyAcceptedReturns=true) {
		//GRAB THE ORDER TOTAL
		$due  = $this->getTotal();
		//DECREMENT PAYMENTS RECEIVED
		$due -= $this->paid;
		//DECREMENT REFUNDS MADE
		//foreach ($this->getRefunds() as $refund) {
		//	$due -= $refund->amount;
		//}

		//LOOK FOR RETURNS WITH BALANCING PAYMENTS
		foreach ($this->getReturns() as $return) {
			if ($return->statusID < self::RETURN_STATUS_PAID &&
				($return->accepted || !$onlyAcceptedReturns)) {
				$due += $return->balancingPayment;
			}
		}
		return $due;
	}


	//GET THE PAID BALANCE ON THE ORDER
	public function amountPaid() {
		return $this->paid;
	}


	//RETURN THE SIMPLE CURRENCYID
	public function getSimpleCurrencyID() {
		$id = explode(':', $this->currencyID);
		end($id);
		return current($id);
	}


	//RETURN A COMPLEX LOCALE_ID:CURRENCY_ID CURRENCY ID
	public function getComplexCurrencyID() {

		$badCurrencyID = array('AU:USD' => 'RW:USD', 'US:GBP' => 'UK:GBP');

		if (!strstr($this->currencyID, ':')) {
			//DETERMINE LOCALE BASED ON DELIVERY ADDRESS
			$localeID = ($this->getAddress('delivery')) ? getLocaleForCountry($this->getAddress('delivery')->countryID) : Locale::DEFAULT_LOCALE_ID;
			$complexCurrencyID = strtoupper($localeID . ':' . $this->currencyID);
			return (isset($badCurrencyID[$complexCurrencyID])? $badCurrencyID[$complexCurrencyID] : $complexCurrencyID);
		}
		return (isset($badCurrencyID[$this->currencyID])? $badCurrencyID[$this->currencyID] : $this->currencyID);
	}


	//RETURN THE METADATA OBJECT FOR ARBITRARY DATA ASSOCIATED WITH THIS ORDER
	public function getMetadata() {
		if (is_null($this->metadata)) {
			$this->metadata = new OrderMetadata($this->orderID);
		}
		return $this->metadata;
	}


	//LOAD ALL COLLECTIONS
	public function loadAll() {
		foreach ($this as $property) {
			if ($property instanceof OrderCollection) {
				$property->load();
			}
		}
	}


	public function getNotes($raisedFrom = false) {
		$this->notes->load();

		if($raisedFrom === false) {
			return $this->notes->getItems();
		}

		$notes = array();

		foreach($this->notes->getItems() as $note) {
			if($note->raisedFrom == $raisedFrom) {
				$notes[] = $note;
			}
		}

		return $notes;
	}

}