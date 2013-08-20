<?php

namespace Message\Mothership\Commerce\Product\Stock;

use Message\Mothership\Commerce\Product\Stock\Movement\Movement;
use Message\Mothership\Commerce\Product\Stock\Movement\Create;
use Message\Mothership\Commerce\Product\Stock\Movement\Adjustment\Adjustment;
use Message\Mothership\Commerce\Product\Stock\Location\Location;
use Message\Mothership\Commerce\Product\Stock\Movement\Reason\Reason;

use Message\Mothership\Commerce\Product\Unit\Unit;
use Message\Mothership\Commerce\Product\Unit\Edit;

use Message\Cog\DB;

/**
 * Stock manager help creating stock movements.
 * It is injected with an adjuster for the stock-level
 * and a create decorator for creating the movements.
 */
class StockManager implements DB\TransactionalInterface
{
	/**
	 * Movement create-decorator used for creating the movement
	 * @var Create
	 */
	protected $_movementCreator;

	/**
	 * Unit Editor used for updating unit-stock-levels
	 * @var Edit
	 */
	protected $_unitEditor;

	/**
	 * The transaction used for updating and saving
	 * @var DB\Transaction
	 */
	protected $_transaction;

	/**
	 * The internal Movement-object, which is built by the StockManager
	 * @var Movement
	 */
	protected $_movement;

	/**
	 * Load dependencies
	 *
	 * @param  Query  	$query  			DB\Transaction Object
	 * @param  Create 	$movementCreator 	Movement create-decorator used for creating the movements
	 * @param  Edit 	$unitEditor 		Unit Editor used for updating unit-stock-levels
	 *
	 * @return $this	for chainability
	 */
	public function __construct(DB\Transaction $transaction, Create $movementCreator, Edit $unitEditor)
	{
		$this->_transaction = $transaction;
		$this->_movementCreator = $movementCreator;
		$this->_unitEditor = $unitEditor;

		$this->_movement = new Movement;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setTransaction(DB\Transaction $transaction)
	{
		$this->_transaction = $transaction;
	
		return $this;
	}

	/**
	 * Sets movement's reason
	 * @param Reason $reason the reason the movement's reason is set to
	 * @return StockManager $this for chainability
	 */
	public function setReason(Reason $reason)
	{
		$this->_movement->reason = $reason;

		return $this;
	}

	/**
	 * Sets movement's automated-field
	 * @param bool $bool the value automated will be set to
	 * @return StockManager $this for chainability
	 */
	public function setAutomated($bool)
	{
		$this->_movement->setAutomated((bool)$bool);
	}

	/**
	 * Sets movement's (optional) note
	 * @param string $note the value the movement's note will be set to
	 * @return StockManager $this for chainability
	 */
	public function setNote($note)
	{
		$this->_movement->note = $note;
		return $this;
	}
	
	/**
	 * Sets the movement the stock manager is working with
	 * If reason or note were already set, using this method
	 * will override former reason and note to the new movement's ones!
	 *
	 * @param Movement $movement the movement to use from now on.
	 * @return StockManager $this for chainability
	 */
	public function setMovement(Movement $movement)
	{
		$this->movement = $movement;
		return $this;
	}


	/**
	 * Creates an adjustment for the given unit and location, with
	 * a given increment-value.
	 * If the value is negative, it will just be turned into a positive one!
	 *
	 * @param Unit 		$unit 		the unit the adjustment will effect
	 * @param Location 	$location 	the stock location
	 * @param int 		$increment 	the amount the stock level will be incremented
	 *								by. Defaults to 1.
	 * @return StockManager $this for chainability
	 */
	public function increment(Unit $unit, Location $location, $increment = 1)
	{
		$adjustment = new Adjustment;

		$adjustment->unit 		= $unit;
		$adjustment->location 	= $location;
		$adjustment->delta 		= abs((int)$increment);

		$this->_movement->addAdjustment($adjustment);

		return $this;
	}

	/**
	 * Creates an adjustment for the given unit and location, with
	 * a given decrement-value.
	 * If the value is negative, it will just be turned into a positive one!
	 *
	 * @param Unit 		$unit 		the unit the adjustment will effect
	 * @param Location 	$location 	the stock location
	 * @param int 		$decrement 	the amount the stock level will be decremented
	 *								by. Defaults to 1.
	 * @return StockManager $this for chainability
	 */
	public function decrement(Unit $unit, Location $location, $decrement = 1)
	{
		$adjustment = new Adjustment;

		$adjustment->unit 		= $unit;
		$adjustment->location 	= $location;
		$adjustment->delta 		= abs((int)$decrement) * -1;

		$this->_movement->addAdjustment($adjustment);

		return $this;
	}

	/**
	 * Creates an adjustment for the given unit and location, with a new
	 * value the stock level will be set to.
	 * No conversion into a positive value will be made!
	 *
	 * @param Unit 		$unit 		the unit the adjustment will effect
	 * @param Location 	$location 	the stock location
	 * @param int 		$value 		the new stock level
	 *
	 * @throws \IllegalArgumentException if the value is negative
	 *
	 * @return StockManager $this for chainability
	 */
	public function set(Unit $unit, Location $location, $value)
	{
		if($value < 0) {
			throw new \IllegalArgumentException("Value set for stock adjustment must be positive!");
		}

		$adjustment 	= new Adjustment;
		$curStockLevel 	= $unit->getStockForLocation($location);

		$adjustment->unit 		= $unit;
		$adjustment->location 	= $location;
		$adjustment->delta 		= $value - $curStockLevel;

		$this->_movement->addAdjustment($adjustment);

		return $this;
	}

	/**
	 * This method saves the created movement.
	 * It tells the $_movementCreator to save the movement and the
	 * $_unitEditor to update the stock according to the movement's adjustments.
	 * Also makes sure everything happens within one transaction.
	 *
	 * @return 	result of $_transaction->commit()
	 * @throws  \LogicException	if no reason is set on the movement
	 */
	public function commit()
	{
		if(!$this->_movement->reason) {
			throw new \LogicException('Cannot save movement without reason!');
		}

		$this->_movementCreator->setTransaction($this->_transaction);
		$this->_unitEditor->setTransaction($this->_transaction);

		$this->_movementCreator->create($this->_movement);

		foreach($this->_movement->adjustments as $adjustment) {
			$unit 	  = $adjustment->unit;
			$location = $adjustment->location;

			// set stock to adjusted level
			$unit->setStockForLocation($this->_getNewStockLevel($adjustment), $location);

			$this->_unitEditor->saveStockForLocation($unit, $location);
		}

		return $this->_transaction->commit();
	}

	/**
	 * @param 	Adjustment 	$adjustment 	the adjustment the new stock level
	 *										will be calculated for
	 * @return 	returns the new total stock level for the adjustment's
	 *			unit and location, after applying adjustment's delta.
	 */
	protected function _getNewStockLevel($adjustment)
	{
		$curLevel = $adjustment->unit->getStockForLocation($adjustment->location);
		return $curLevel + $adjustment->delta;
	}
}