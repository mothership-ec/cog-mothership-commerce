<?php

namespace Message\Mothership\Commerce\Order;

use Message\Mothership\Commerce\User\LoaderInterface;
use Message\Mothership\Commerce\Product\Unit\Unit;
use Message\User\UserInterface;
use Message\Cog\Localisation\Locale;

// $basket = $this->get('basket');

// $basket->addItem($unit);
// $basket->updateQuantity($unit, 3);

// $basket->removeItem($unit);
// $basket->empty();

// $basket->addVoucher($giftVoucherCode);
// $basket->addDiscount($discountCode);

// $basket->hasAddresses();
// $basket->updateAddress(new Address('line1'), 'delivery');

// $basket->setShipping($shippingOption);

// $basket->getOrder();

// $basket->getOrder()->items;

/**
 *
 *
 * @author Danny Hannah <danny@message.co.uk>
 */
class Assembler
{

	protected $_user;
	protected $_locale;
	protected $_order;

	public function __construct(Order $order, UserInterface $user, Locale $locale)
	{
		$this->_order  = $order;
		$this->_user   = $user;
		$this->_locale = $locale;
	}


	public function addItem(Unit $unit)
	{
		$item = new Entity\Item\Item;
		$this->_order->getItems()->append($item::createFromUnit($unit));

		return $this;
	}

	public function updateQuantity(Unit $unit, $quantity = 1)
	{
		// Count how many times this unit is already in the basket
		$unitCount = $this->_countForUnitID($unit);

		// Remove the item if the quantity is 0
		if ($quantity < 1) {
			return $this->_removeItem($unit);
		}

		// If the quantities are the same then return
		if ($unitCount == $quantity) {
			return $this;
		}

		// Add the item to the order as many times to make the count equal to
		// the given quantity
		if ($quantity > $unitCount) {
			for ($i = $unitCount ; $i == $quantity; $i++) {
				$this->addItem($unit);
			}
			return $this;
		}

		// Remove the item as many times needed to make the count equal the given
		// quantity
		if ($quantity < $unitCount) {
			for ($i = $unitCount ; $i == $quantity; $i--) {
				$this->removeItem($unit);
			}
			return $this;
		}
	}

	public function removeItem(Unit $unit)
	{
		$this->_order->getItems()->append($item::createFromUnit($unit));
	}

	public function getItemQuantity(Unit $unit)
	{
		return $this->_countForUnitID($unit);
	}

	public function emptyBasket()
	{
	}

	public function addVoucher()
	{

	}

	public function removeVoucher()
	{

	}

	public function addDiscount()
	{

	}

	public function removeDiscount()
	{

	}

	public function hasAddress()
	{

	}

	public function updateAddress(Address $address, $type = '')
	{

	}

	public function setShipping()
	{

	}

	public function getOrder()
	{
		return $this->_order;
	}

	protected function _countForUnitID(Unit $unit)
	{
		return count($this->_order->getItems()->getByProperty('unitID', $unit->id));
	}

}