<?php

namespace Message\Mothership\Commerce\Order;

use Message\Cog\Service\Container;
use Message\Cog\ValueObject\Authorship;

/**
 * Order model. Container for all information about an order.
 *
 * @todo currency symbol??
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Order
{
	public $id;
	public $orderID; // alias of $id for BC

	public $user;
	public $authorship;
	public $status;

	public $type;
	public $locale;
	public $currencyID;
	public $conversionRate    = 0;

	public $productNet        = 0;
	public $productDiscount   = 0;
	public $productTax        = 0;
	public $productGross      = 0;

	public $totalNet          = 0;
	public $totalDiscount     = 0;
	public $totalTax          = 0;
	public $totalGross        = 0;

	public $shippingName;
	public $shippingListPrice = 0;
	public $shippingNet       = 0;
	public $shippingDiscount  = 0;
	public $shippingTax       = 0;
	public $shippingTaxRate   = 0;
	public $shippingGross     = 0;

	public $metadata;

	protected $_entities = array();

	/**
	 * Constructor.
	 *
	 * @param array $entities An array of order entities to use, where the key
	 *                        is the entity name and the value is the loader
	 */
	public function __construct(array $entities = array())
	{
		$this->authorship = new Authorship;

		foreach ($entities as $name => $loader) {
			$this->addEntity($name, $loader);
		}
	}

	/**
	 * Magic getter. This maps to defined order entities.
	 *
	 * @param  string $var       Entity name
	 *
	 * @return Entity\Collection The entity collection instance
	 *
	 * @throws \InvalidArgumentException If an entity with the given name doesn't exist
	 */
	public function __get($var)
	{
		if (!array_key_exists($var, $this->_entities)) {
			throw new \InvalidArgumentException(sprintf('Order entity `%s` does not exist', $var));
		}

		return $this->_entities[$var];
	}

	/**
	 * Magic isset. This maps to defined order entities.
	 *
	 * @param  string  $var Entity name
	 *
	 * @return boolean      True if the entity exist
	 */
	public function __isset($var)
	{
		return array_key_exists($var, $this->_entities);
	}

	/**
	 * Add an entity to this order.
	 *
	 * @param string                 $name   Entity name
	 * @param Entity\LoaderInterface $loader Entity loader
	 *
	 * @throws \InvalidArgumentException If an entity with the given name already exists
	 */
	public function addEntity($name, Entity\LoaderInterface $loader)
	{
		if (array_key_exists($name, $this->_entities)) {
			throw new \InvalidArgumentException(sprintf('Order entity already exists with name `%s`', $name));
		}

		$this->_entities[$name] = new Entity\Collection($this, $loader);
	}

	public function getEntities()
	{
		return $this->_entities;
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

	/**
	 * Get the address for this order of a specific type.
	 *
	 * @param  string $type                 The address type
	 *
	 * @return Entity\Address\Address|false The address, or false if it was not
	 *                                      found
	 *
	 * @throws \BadMethodCallException   If the addresses entity is not set
	 * @throws \UnexpectedValueException If more than one address of this type
	 *                                   was found
	 */
	public function getAddress($type)
	{
		if (!array_key_exists('addresses', $this->_entites)) {
			throw new \BadMethodCallException(sprintf(
				'Cannot get `%s` addresses as the addresses entity is not set on this order',
				$type
			));
		}

		$addresses = $this->_entites['addresses']->getByProperty('type', $type);

		if (count($addresses) > 1) {
			throw new \UnexpectedValueException(sprintf(
				'Order has more than one `%s` address',
				$type
			));
		}

		return current($addresses) ?: false;
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
		return $this->_entities['discounts'];
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
	public function amountPaid(){
		return $this->paid;
	}


	/**
	 * Get the "simple" currency ID for this order (the currency ID without the
	 * locale ID).
	 *
	 * This is still here for backwards-compatibility and is due to be removed
	 * in a future version.
	 *
	 * @return string The currency ID
	 */
	public function getSimpleCurrencyID()
	{
		return $this->currencyID;
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


	/**
	 * Get the metadata for this order.
	 *
	 * This is still here for backwards-compatibility and is due to be removed
	 * in a future version.
	 *
	 * @return Metadata
	 */
	public function getMetadata()
	{
		return $this->metadata;
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