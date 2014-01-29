<?php

namespace Message\Mothership\Commerce\Order;

use Message\Mothership\Commerce\Shipping;
use Message\Mothership\Commerce\Product\Unit\Unit;
use Message\Mothership\Commerce\Product\Stock\Location\Location as StockLocation;

use Message\User\User;

use Message\Cog\Event\DispatcherInterface;

/**
 * Provides a simpler interface for building an instance of Order.
 *
 * @author Danny Hannah <danny@message.co.uk>
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Assembler
{
	protected $_order;
	protected $_eventDispatcher;
	protected $_defaultStockLocation;

	protected $_dispatchEvents = true;

	/**
	 * Constructor.
	 *
	 * @param Order               $order                The order to assemble
	 * @param DispatcherInterface $dispatcher           The event dispatcher
	 * @param string|int          $defaultStockLocation The default stock
	 *                                                  location to use for items
	 */
	public function __construct(Order $order, DispatcherInterface $dispatcher, $defaultStockLocation)
	{
		$this->_order           = $order;
		$this->_eventDispatcher = $dispatcher;

		$this->setDefaultStockLocation($defaultStockLocation);
	}

	/**
	 * Set the default stock location to set for items added to the order.
	 *
	 * @param string|int $stockLocation
	 */
	public function setDefaultStockLocation($stockLocation)
	{
		$this->_defaultStockLocation = $stockLocation;
	}

	public function getOrder()
	{
		return $this->_order;
	}

	public function setOrder(Order $order)
	{
		$this->_order = $order;

		return $this->dispatchEvent();
	}

	/**
	 * Add an entity to the order.
	 *
	 * If the entity has an `order` property (they all should, really), it is
	 * set to the order that is being assembled.
	 *
	 * @param string                 $name   The entity name
	 * @param Entity\EntityInterface $entity The entity
	 *
	 * @return Assembler                     Returns $this for chainability
	 */
	public function addEntity($name, Entity\EntityInterface $entity)
	{
		if (property_exists($entity, 'order')) {
			$entity->order = $this->_order;
		}

		$this->_order->{$name}->append($entity);

		return $this->dispatchEvent();
	}

	public function addUnit(Unit $unit, $stockLocation = false)
	{
		$item = new Entity\Item\Item;
		$item->order = $this->_order;

		$item->populate($unit);

		$item->stockLocation = $stockLocation ?: $this->_defaultStockLocation;

		return $this->addItem($item);
	}

	public function addItem(Entity\Item\Item $item, $fireEvent = true)
	{
		$item->order = $this->_order;

		if (!($item->stockLocation instanceof StockLocation)) {
			throw new \InvalidArgumentException('Cannot add item to order: must have a valid stock location set');
		}

		$this->_order->items->append($item);

		if ($fireEvent) {
			$this->dispatchEvent();
		}

		return $this;
	}

	/**
	 * Add a note to the assembler's order.
	 *
	 * @param Entity\Note\Note $note
	 * @return Assembler
	 */
	public function addNote(Entity\Note\Note $note)
	{
		$note->order = $this->_order;

		$this->_order->notes->append($note);

		// WHY DOES THIS NOT DISPATCH EVENT?

		return $this;
	}

	/**
	 * Set the note for the order.
	 *
	 * @param  Entity\Note\Note $note
	 * @return Assembler
	 */
	public function setNote(Entity\Note\Note $note)
	{
		// Clear the order notes before adding the new entity
		$this->_order->notes->clear();

		$this->addNote($note);

		return $this;
	}

	public function updateQuantity(Unit $unit, $quantity = 1)
	{
		// Disable event dispatching while we update the quantities
		$this->_dispatchEvents = false;

		// Count how many times this unit is already in the basket
		$unitCount = $this->_countForUnitID($unit);
		// Load the items from the basket which already have this unitID
		$items = $this->_order->items->getByProperty('unitID', $unit->id);

		// If the quantities are the same then return
		if ($unitCount == $quantity) {
			return $this;
		}

		// Remove the item as many times needed to make the count equal the given
		// quantity
		if ($quantity < $unitCount) {
			for ($i = $unitCount ; $i > $quantity; $i--) {
				$this->_order->items->remove(array_shift($items));
			}
		}

		// Add the item to the order as many times to make the count equal to
		// the given quantity
		if ($quantity > $unitCount) {
			$item = array_shift($items);
			for ($i = $unitCount; $i < $quantity; $i++) {
				$this->addUnit($unit, $item->stockLocation);
			}
		}

		// Re-enable event dispatching now we're done
		$this->_dispatchEvents = false;

		// Dispatch the update event
		return $this->dispatchEvent();
	}

	public function setType($type)
	{
		$this->_order->type = $type;

		return $this;
	}

	public function setUser(User $user)
	{
		$this->_order->user = $user;

		return $this->dispatchEvent();
	}

	/**
	 * @see setUser
	 * @deprecated Use setUser() instead. To be removed in 2.0.
	 */
	public function addUser(User $user)
	{
		return $this->setUser($user);
	}

	/**
	 * Add a discount to the order.
	 *
	 * @see addEntity
	 *
	 * @param Entity\Discount\Discount $discount The discount to add
	 *
	 * @return Assembler                         Returns $this for chainability
	 */
	public function addDiscount(Entity\Discount\Discount $discount)
	{
		return $this->_addEntity('discount', $discount);
	}

	public function removeDiscount(Entity\Discount\Discount $discount)
	{
		$this->_order->discounts->removeByCode($discount->code);

		return $this->dispatchEvent();
	}

	public function addPayment(Entity\Payment\MethodInterface $paymentMethod, $amount, $reference, $silenceEvent = false)
	{
		$payment            = new Entity\Payment\Payment;
		$payment->method    = $paymentMethod;
		$payment->amount    = $amount;
		$payment->order     = $this->_order;
		$payment->reference = $reference;
		$payment->id 		= $reference;

		foreach ($this->_order->payments->all() as $checkPayment) {
			if ($checkPayment->reference == $payment->reference) {
				return false;
			}
		}

		$this->_order->payments->append($payment);

		if (!$silenceEvent) {
			$this->dispatchEvent();
		}

		return $this;
	}

	public function addAddress(Entity\Address\Address $address)
	{
		if (is_null($address->forename)) {
			$address->forename = $this->_order->user->forename;
		}

		if (is_null($address->surname)) {
			$address->surname = $this->_order->user->surname;

		}

		if (is_null($address->title)) {
			$address->title = $this->_order->user->title;
		}

		// ID is set as the type so this will remove all the address types from the
		// basket so we only have one billing and one delivery address
		$this->_order->addresses->remove($address->id);

		$this->_order->addresses->append($address);

		return $this->dispatchEvent();
	}

	/**
	 * This is used when the user logs out and we need to clear all the addresses
	 * from the basket as a log out doesn't empty the basket.
	 *
	 * @return $this
	 */
	public function removeAddresses()
	{
		if (count($this->getOrder()->addresses)) {
			foreach ($this->getOrder()->addresses as $address) {
				$this->_order->addresses->remove($address->id);
			}
		}

		return $this;
	}

	public function setShipping(Shipping\MethodInterface $option)
	{
		$this->_order->shippingName        = $option->getName();
		$this->_order->shippingDisplayName = $option->getDisplayName();
		$this->_order->shippingListPrice   = $option->getPrice();

		return $this->dispatchEvent();
	}

	/**
	 * Dispatch the ASSEMBLER_UPDATE event and set the order to whatever is
	 * returned from the event's `getOrder()` method (this allows listeners to
	 * overwrite the order).
	 *
	 * Note that the event won't dispatch if `$this->_dispatchEvents` is not
	 * true. This allows the assembler to avoid many events being dispatched
	 * when the item quantity is being changed, for example.
	 *
	 * @return Assembler Returns $this for chainability
	 */
	public function dispatchEvent()
	{
		if (true === $this->_dispatchEvents) {
			$this->_order = $this->_eventDispatcher->dispatch(
				Events::ASSEMBLER_UPDATE,
				new Event\Event($this->_order)
			)->getOrder();
		}

		return $this;
	}

	protected function _countForUnitID(Unit $unit)
	{
		return count($this->_order->items->getByProperty('unitID', $unit->id));
	}
}