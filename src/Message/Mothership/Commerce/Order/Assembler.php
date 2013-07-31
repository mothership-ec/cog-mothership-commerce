<?php

namespace Message\Mothership\Commerce\Order;

use Message\Mothership\Commerce\User\LoaderInterface;
use Message\Mothership\Commerce\Product\Unit\Unit;
use Message\User\UserInterface;
use Message\Cog\Localisation\Locale;
use Message\Mothership\Commerce\Order\Entity\Item\Item;

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
		$item->order = $this->_order;
		$this->_order->items->append($item->createFromUnit($unit));

		return $this;
	}

	public function removeItem(Item $item)
	{
		$this->_order->items->remove($item->id);

		return $this;
	}

	public function updateQuantity(Unit $unit, $quantity = 1)
	{
		// Count how many times this unit is already in the basket
		$unitCount = $this->_countForUnitID($unit);
		// Load the itesm from the basket which already have this unitID
		$items = $this->_order->items->getByProperty('unitID', $unit->id);

		// If the quantities are the same then return
		if ($unitCount == $quantity) {
			return $this;
		}

		// Remove the item as many times needed to make the count equal the given
		// quantity
		if ($quantity < $unitCount) {
			for ($i = $unitCount ; $i > $quantity; $i--) {
				$this->removeItem(array_shift($items));
			}
		}

		// Add the item to the order as many times to make the count equal to
		// the given quantity
		if ($quantity > $unitCount) {
			for ($i = $unitCount; $i < $quantity; $i++) {
				$this->addItem($unit);
			}
		}

		return $this;
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
		return count($this->_order->items->getByProperty('unitID', $unit->id));
	}

}