<?php

namespace Message\Mothership\Commerce\Product\Stock\Movement;

/**
 * This stock movement iterator is used to work out the history of the
 * stock movements for a given Unit
 */
class Iterator implements \Iterator
{
	protected $_product;
	protected $_movements;
	protected $_counter;

	public function __construct(Product $product)
	{
		$this->_product = $product;
		$this->_loadStockLevels();
		$this->_loadMovements();
	}

    /**
     * Resests the pointer of the iteration
     *
     * @access public
     */
	public function rewind()
	{
		$this->_loadStockLevels();
	}

    /**
     * Moves the pointer forward and returns the next stock movement
     *
     * @return StockMovement Object
     * @access public
     */
	public function next()
	{
		foreach ($this->current()->unitDelta as $unitID => $data) {
		    foreach ($data as $locationID => $adjustment) {
		    	$this->_counter[$unitID][$locationID] += (int) $adjustment * -1; // flip it
		    }
		}

		return next($this->_movements);
	}

    /**
     * Returns the stockMovementID for the current stock movement
     *
     * @return int stockMovementID
     * @access public
     */
	public function key()
	{
		return key($this->_movements);
	}

    /**
     * Returns the current StockMovement object
     *
     * @return StockMovement Object
     * @access public
     */
	public function current()
	{
		return current($this->_movements);
	}

    /**
     * Returns the stock level for a specific movement given a movement ID, unit ID and location ID
     *
     * @param int $movementID
     * @param int $unitID
     * @param int $locationID
     * @return int stock level
     * @access public
     */
    public function getStockForMovement($movementID, $unitID, $locationID) {

        while($movement = $this->next()) {

            if($movement->stockMovementID == $movementID) {

                return $this->_counter[$unitID][$locationID];
            }
        }

    }

    /**
     * This method gets the current stock adjustment and the current stock
     * count and takes the $adjustment figure away from the $after figure
     * to leave the stock level before the stock movement
     *
     * @param unknown $unitID
     * @param unknown $locationID
     * @return number
     * @access public
     */
	public function getStockBefore($unitID, $locationID)
	{
		$adjustment = $this->current()->unitDelta[$unitID][$locationID];
		$after      = $this->getStockAfter($unitID, $locationID);

		return $after - $adjustment;
	}

    /**
     * Returns the stock level after the movement has been processed.
     * this works as next() has already been called and adjusted the _counter
     *
     * @param unknown $unitID.
     * @param unknown $locationID
     * @return number
     * @access public
     */
	public function getStockAfter($unitID, $locationID)
	{
		// throw exception if not set
		return $this->_counter[$unitID][$locationID];
	}

    public function valid()
    {
        $key = key($this->_movements);
        $var = ($key !== NULL && $key !== FALSE);
        return $var;
    }

    /**
     * Loads all the stock movements for the given catalogueID
     *
     * @access protected
     */
	protected function _loadMovements()
	{
		$stockMovementIDs = StockMovement::loadStockMovementIDsForCatalogueID($this->_product->catalogueID);
		$stockMovement = array();
		foreach($stockMovementIDs as $stockMovementID) {
			$stockMovement[$stockMovementID] = new StockMovement($stockMovementID);
		}
		$this->_movements = $stockMovement;

	}

    /**
     * Loads the stock to an array grouped by unitID and then by locationID
     *
     * @return void
     * @access protected
     */
	protected function _loadStockLevels()
	{
		foreach($this->_product->getUnits() as $unit ) {
			foreach($unit->stock as $locationID => $stock) {
				$this->_counter[$unit->unitID][$locationID] = ($stock ? $stock : 0);
			}
		}
	}

}