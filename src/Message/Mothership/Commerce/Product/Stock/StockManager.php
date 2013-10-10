<?php

namespace Message\Mothership\Commerce\Product\Stock;

use Message\Mothership\Commerce\Product\Stock\Movement;
use Message\Mothership\Commerce\Product\Stock\Movement\Adjustment;
use Message\Mothership\Commerce\Product\Stock\Location\Location;
use Message\Mothership\Commerce\Product\Stock\Movement\Reason\Reason;

use Message\Mothership\Commerce\Product\Events;

use Message\Mothership\Commerce\Product\Unit\Unit;
use Message\Mothership\Commerce\Product\Unit\Edit;

use Message\Cog\Event\Dispatcher;
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
	 * Adjustment create-decorator for creating adjustments
	 */
	protected $_adjustmentCreator;

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
	 * Flag giving information about whether the insert-row for the movement
	 * was already added to the transaction or not
	 * @var bool
	 */
	protected $_movementInTransaction = false;

	/**
	 * Event Dispatcher Object
	 */
	protected $_eventDispatcher;

	/**
	 * Load dependencies
	 *
	 * @param  Query  	$query  			DB\Transaction Object
	 * @param  Create 	$movementCreator 	Movement create-decorator used for creating the movements
	 * @param  Edit 	$unitEditor 		Unit Editor used for updating unit-stock-levels
	 *
	 * @return $this	for chainability
	 */
	public function __construct(DB\Transaction $transaction, Movement\Create $movementCreator,
		Adjustment\Create $adjustmentCreator, Edit $unitEditor, Dispatcher $eventDispatcher)
	{
		$this->_transaction = $transaction;
		$this->_movementCreator = $movementCreator;
		$this->_adjustmentCreator = $adjustmentCreator;
		$this->_unitEditor = $unitEditor;
		$this->_eventDispatcher = $eventDispatcher;

		$this->_movement = new Movement\Movement;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setTransaction(DB\Transaction $transaction)
	{
		$this->_checkMovementNotInTransaction();

		$this->_transaction = $transaction;
		$this->_adjustmentCreator->setTransaction($transaction);
		$this->_movementCreator->setTransaction($transaction);
		$this->_unitEditor->setTransaction($transaction);

		return $this;
	}

	/**
	 * Sets movement's reason
	 * @param Reason $reason the reason the movement's reason is set to
	 * @return StockManager $this for chainability
	 */
	public function setReason(Reason $reason)
	{	
		$this->_checkMovementNotInTransaction();
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
		$this->_checkMovementNotInTransaction();
		$this->_movement->automated = (bool)$bool;
		return $this;
	}

	/**
	 * Sets movement's (optional) note
	 * @param string $note the value the movement's note will be set to
	 * @return StockManager $this for chainability
	 */
	public function setNote($note)
	{
		$this->_checkMovementNotInTransaction();
		$this->_movement->note = $note;
		return $this;
	}

	public function createWithRawNote($bool) {
		$this->_checkMovementNotInTransaction();
		$this->_movementCreator->createWithRawNote((bool)$bool);
	}
	
	/**
	 * Sets the movement the stock manager is working with
	 * If reason or note were already set, using this method
	 * will override former reason and note to the new movement's ones!
	 *
	 * @param Movement $movement the movement to use from now on.
	 * @return StockManager $this for chainability
	 */
	public function setMovement(Movement\Movement $movement)
	{
		$this->_checkMovementNotInTransaction();
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
		$adjustment = new Adjustment\Adjustment;

		$adjustment->unit 		= $unit;
		$adjustment->location 	= $location;
		$adjustment->delta 		= abs((int)$increment);

		$this->_saveNewAdjustment($adjustment);
		
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
		$adjustment = new Adjustment\Adjustment;

		$adjustment->unit 		= $unit;
		$adjustment->location 	= $location;
		$adjustment->delta 		= abs((int)$decrement) * -1;

		$this->_saveNewAdjustment($adjustment);

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

		$adjustment 	= new Adjustment\Adjustment;
		$curStockLevel 	= $unit->getStockForLocation($location);

		$adjustment->unit 		= $unit;
		$adjustment->location 	= $location;
		$adjustment->delta 		= $value - $curStockLevel;

		$this->_saveNewAdjustment($adjustment);
		
		return $this;
	}

	/**
	 * Commits transaction and (if successful) fires stock movement event
	 * @return 	bool true if commit was successful
	 */
	public function commit()
	{
		$commit = $this->_transaction->commit();

		if($commit) {
			$this->_eventDispatcher->dispatch(
				Events::STOCK_MOVEMENT,
				new Movement\MovementEvent($this->_movement)
			);
			return true;
		}

		return false;
	}

	/**
	 * @param 	Adjustment 	$adjustment 	the adjustment the new stock level
	 *										will be calculated for
	 * @return 	returns the new total stock level for the adjustment's
	 *			unit and location, after applying adjustment's delta.
	 */
	protected function _getAdjustedStockLevel($adjustment)
	{
		$curLevel = $adjustment->unit->getStockForLocation($adjustment->location);
		return $curLevel + $adjustment->delta;
	}

	/**
	 * Checks whether movement has not yet been added to transaction
	 * @throws \LogicException if movement was already added
	 */
	protected function _checkMovementNotInTransaction()
	{
		if($this->_movementInTransaction) {
			throw new \LogicException('You can only change the movement before adding adjustments to it!');
		}		
	}

	/**
	 * Just checks whether a reason is already set on the movement
	 * @throws \LogicException if no reason is set
	 */
	protected function _checkReasonSet()
	{
		if(!$this->_movement->reason) {
			throw new \LogicException('Cannot save movement without reason!');
		}
	}

	/**
	 * Just whether movement has already been added to transaction
	 * and adds it, if not.
	 */
	protected function _addMovementToTransactionIfNeeded()
	{
		if(!$this->_movementInTransaction) {
			$this->_movementCreator->setTransaction($this->_transaction);
			$this->_movementCreator->createWithoutAdjustments($this->_movement);

			$this->_movementInTransaction = true;
		}
	}

	/**
	 * Saves new adjustment (and if needed) the movement to the transaction
	 * and adds the adjustment to the movement. The method then saves the
	 * stock level-update to the transaction.
	 *
	 * @param  Adjustment\Adjustment $adjustment the new adjustment
	 * @return Adjustment\Adjustment the adjustment
	 */
	protected function _saveNewAdjustment(Adjustment\Adjustment $adjustment)
	{
		$this->_checkReasonSet();
		$this->_addMovementToTransactionIfNeeded();

		$this->_movement->addAdjustment($adjustment);

		$this->_adjustmentCreator->setTransaction($this->_transaction);
		$adjustment = $this->_adjustmentCreator->create($adjustment);

		$unit 	  = $adjustment->unit;
		$location = $adjustment->location;

		// set stock to adjusted level
		$unit->setStockForLocation($this->_getAdjustedStockLevel($adjustment), $location);
		$this->_unitEditor->setTransaction($this->_transaction);
		$this->_unitEditor->saveStockForLocation($unit, $location);

		return $adjustment;
	}
}