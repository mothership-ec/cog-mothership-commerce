<?php

namespace Message\Mothership\Commerce\Product\Stock\Movement;

use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\Unit\Unit;
use Message\Mothership\Commerce\Product\Stock\Location;


/**
 * This stock movement iterator is used to work out the history of the
 * stock movements for a given Product
 */
class Iterator implements \Iterator
{
	protected $_product = null;
    protected $_movementLoader;
    protected $_locations;
	protected $_movements = array();
	protected $_counter;

	public function __construct(Loader $movementLoader, Location\Collection $locations)
	{
		$this->_movementLoader = $movementLoader;
        $this->_locations = $locations;
	}

    public function setProduct(Product $product)
    {
        $this->_product = $product;

        $this->_movements = $this->_movementLoader->getByProduct($this->_product);
        $this->_loadStockLevels();

        return $this;
    }

    /**
     * Resests the pointer of the iteration
     *
     */
	public function rewind()
	{
		$this->_loadStockLevels();
        return reset($this->_movements);
	}

    /**
     * Moves the pointer forward and returns the next stock movement
     *
     * @return StockMovement Object
     */
	public function next()
	{
        $this->_checkProductSet();

        foreach ($this->current()->adjustments as $adjustment) {
            $this->_counter[$adjustment->unit->id][$adjustment->location->name] += (int)($adjustment->delta * -1); // flip it
        }

		return next($this->_movements);
	}

    /**
     * Returns the stockMovementID for the current stock movement
     *
     * @return int stockMovementID
     */
	public function key()
	{
        $this->_checkProductSet();

		return key($this->_movements);
	}

    /**
     * Returns the current StockMovement object
     *
     * @return StockMovement Object
     */
	public function current()
	{
        $this->_checkProductSet();

		return current($this->_movements);
	}

    /**
     * Returns the stock level for a specific movement given a movement ID, unit ID and location ID
     *
     * @param Movement $movement
     * @param Unit $unit
     * @param Location\Location $location
     * @return int stock level
     */
    public function getStockForMovement(Movement $movement, Unit $unit, Location\Location $location) {
        $this->_checkProductSet();

        while($curMovement = $this->next()) {
            if($curMovement == $movement) {
                return $this->_counter[$unit->id][$location->name];
            }
        }

    }

    public function hasStock(Unit $unit, Location\Location $location)
    {
        $this->_checkProductSet();

        foreach($this->current()->adjustments as $adjustment) {
            if($adjustment->unit == $unit && $adjustment->location == $location) {
                return true;
            }
        }

        return false;
    }

    public function getLastStock(Unit $unit, Location\Location $location)
    {
        if(!isset($this->_counter[$unit->id][$location->name])) {
            $this->_counter[$unit->id][$location->name] = $unit->getStockForLocation($location);
        }
        return $this->_counter[$unit->id][$location->name];
    }

    /**
     * This method gets the current stock adjustment and the current stock
     * count and takes the $adjustment figure away from the $after figure
     * to leave the stock level before the stock movement
     *
     * @param Unit $unit
     * @param Location\Location $location
     * @return number
     */
	public function getStockBefore(Unit $unit, Location\Location $location)
	{
        $this->_checkProductSet();

        foreach($this->current()->adjustments as $adjustment) {
            if($adjustment->unit == $unit && $adjustment->location == $location) {
                $after = $this->getStockAfter($unit, $location);
                return $after - $adjustment->delta;
            }
        }
	}

    /**
     * Returns the stock level after the movement has been processed.
     * this works as next() has already been called and adjusted the _counter
     *
     * @param Unit $unit
     * @param Location\Location $location
     * @return number
     */
	public function getStockAfter(Unit $unit, Location\Location $location)
	{
        $this->_checkProductSet();

		// throw exception if not set
		return $this->_counter[$unit->id][$location->name];
	}

    public function valid()
    {
        $key = key($this->_movements);
        return ($key !== NULL && $key !== FALSE);
    }

    /**
     * Loads the stock to an array grouped by unitID and then by locationID
     *
     * @return void
     */
	protected function _loadStockLevels()
	{
        $this->_checkProductSet();

		foreach($this->_product->getUnits() as $unit ) {
			foreach($this->_locations->all() as $location) {
				$this->_counter[$unit->id][$location->name] = $unit->getStockForLocation($location);
			}
		}
	}

    protected function _checkProductSet()
    {
        if(!$this->_product) {
            throw new \LogicException('To use the iterator you first have to set a product!');
        }
    }

}