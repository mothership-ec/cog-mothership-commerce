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
	protected $_movementCreator;
	protected $_unitEditor;
	protected $_transaction;
	protected $_movement;

	/**
	 * Load dependencies
	 *
	 * @param Query  $query  DB\Transaction Object
	 */
	public function __construct(DB\Transaction $transaction, Create $movementCreator, Edit $unitEditor)
	{
		$this->_transaction = $transaction;
		$this->_movementCreator = $movementCreator;
		$this->_unitEditor = $unitEditor;

		$this->_movement = new Movement;

		return $this;
	}

	public function setTransaction(DB\Transaction $transaction)
	{
		$this->_transaction = $transaction;
	
		return $this;
	}

	/**
	 * Sets movement's reason
	 */
	public function setReason(Reason $reason)
	{
		$this->_movement->reason = $reason;

		return $this;
	}

	public function setAutomated($bool)
	{
		$this->_movement->setAutomated((bool)$bool);
	}

	/**
	 * Sets movement's note
	 */
	public function setNote($note)
	{
		$this->_movement->note = $note;
		return $this;
	}
	
	/**
	 * Sets the movement the stock manager is working with to a given one
	 */
	public function setMovement(Movement $movement)
	{
		$this->movement = $movement;
		return $this;
	}


	/**
	 * Creates an adjustment for the given unit and location, with
	 * a given increment-value.
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
	 * The delta
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

	protected function _getNewStockLevel($adjustment)
	{
		$curLevel = $adjustment->unit->getStockForLocation($adjustment->location);
		return $curLevel + $adjustment->delta;
	}
}