<?php

namespace Message\Mothership\Commerce\Product\Stock\Movement;

use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\Unit\Unit;
use Message\Mothership\Commerce\Product\Stock\Location;


/**
 * This stock movement iterator is used for iterating over the stock history
 * of a specific Product.
 * Exception will be thrown if the methods are called without a product being set!
 */
class Iterator implements \Iterator
{
	protected $_units = array();
    protected $_movementLoader;
    protected $_locations;
	protected $_movements = array();
	protected $_counter;

    /**
     * Loads dependencies.
     *
     * @param Loader                $movementLoader Loader for getting all movments for the product
     * @param Location\Collection   $locations      Location Collection used for getting all stock locations
     */
	public function __construct(Loader $movementLoader, Location\Collection $locations)
	{
		$this->_movementLoader = $movementLoader;
        $this->_locations = $locations;
	}

    /**
     * Adds a whole array of units.
     * @param  array $units the units to be added
     * @return $this for chainability
     */
    public function addUnits($units)
    {
        if(!is_array($units)) {
            $units = (array)$units;
        }
        foreach($units as $unit) {
            $this->addUnit($unit);
        }

        return $this;
    }

    /**
     * Adds a new unit to the units-array, adds the movements for this
     * unit to the movements-array and sorts it using the createdAt-date.
     *
     * @param  Unit $unit unit to be added
     * @return $this for chainability
     */
    public function addUnit(Unit $unit)
    {
        $this->_units[] = $unit;
        $this->_movements = array_merge($this->_movements, $this->_movementLoader->getByUnit($unit));

        usort($this->_movements, function($a, $b) {
            $dateA = $a->authorship->createdAt();
            $dateB = $b->authorship->createdAt();

            if($dateA == $dateB) {
                return 0;
            }
            return $dateA > $dateB ? -1 : 1;
        });

        $this->_loadStockLevels();

        return $this;
    }

    public function getMovements()
    {
        return $this->_movements;
    }

    /**
     * Resests the pointer of the iteration and returns first movement
     */
	public function rewind()
	{
		$this->_loadStockLevels();
        return reset($this->_movements);
	}

    /**
     * Moves the pointer forward and returns the next stock movement.
     * Uses _checkUnitsSet-method to make sure a product was already set!
     *
     * @return Movement new current movement
     */
	public function next()
	{
        $this->_checkUnitsSet();

        foreach ($this->current()->adjustments as $adjustment) {
            $this->_counter[$adjustment->unit->id][$adjustment->location->name] += (int)($adjustment->delta * -1); // flip it
        }

		return next($this->_movements);
	}

    /**
     * {@inheritdoc}
     */
	public function key()
	{
		return key($this->_movements);
	}

    /**
     * Returns the current Movement object
     *
     * @return Movement Object
     */
	public function current()
	{
		return current($this->_movements);
	}

    /**
     * Returns the stock level for a specific movement given a movement, unit and location
     * Uses _checkUnitsSet-method to make sure a product was already set!
     *
     * @param Movement          $movement
     * @param Unit              $unit
     * @param Location\Location $location
     *
     * @return int stock level
     */
    public function getStockForMovement(Movement $movement, Unit $unit, Location\Location $location) {
        $this->_checkUnitsSet();

        while($curMovement = $this->next()) {
            if($curMovement == $movement) {
                return $this->_counter[$unit->id][$location->name];
            }
        }

    }

    /**
     * Method determining whether there is stock-information for a
     * specific unit and location in the current movement.
     * Uses _checkUnitsSet-method to make sure a product was already set!
     *
     * @param  Unit $unit
     * @param  Location\Location $location
     *
     * @return bool false if no adjustment for unit and location is found
     *              in the current movement
     */
    public function hasStock(Unit $unit, Location\Location $location)
    {
        $this->_checkUnitsSet();

        foreach($this->current()->adjustments as $adjustment) {
            if($adjustment->unit == $unit && $adjustment->location == $location) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the last stock-level in the stock-history, using the internal
     * $_counter-array.
     * Uses _checkUnitsSet-method to make sure a product was already set!
     *
     * @return int last stock level, if never adjusted the current stock level is returned
     */
    public function getLastStock(Unit $unit, Location\Location $location)
    {
        $this->_checkUnitsSet();

        if(!isset($this->_counter[$unit->id][$location->name])) {
            $this->_counter[$unit->id][$location->name] = $unit->getStockForLocation($location);
        }
        return $this->_counter[$unit->id][$location->name];
    }

    /**
     * This method gets the current stock adjustment and the current stock
     * count and takes the $adjustment figure away from the $after figure
     * to leave the stock level before the stock movement.
     * Uses _checkUnitsSet-method to make sure a product was already set!
     *
     * @param  Unit $unit
     * @param  Location\Location $location
     * @return int  stock level before adjustment.
     *              If no adjustment is found, false is returned.
     */
	public function getStockBefore(Unit $unit, Location\Location $location)
	{
        $this->_checkUnitsSet();

        foreach($this->current()->adjustments as $adjustment) {
            if($adjustment->unit == $unit && $adjustment->location == $location) {
                $after = $this->getStockAfter($unit, $location);
                return $after - $adjustment->delta;
            }
        }

        return false;
	}

    /**
     * Returns the stock level after the movement has been processed.
     * this works as next() has already been called and adjusted the _counter.
     * Uses _checkUnitsSet-method to make sure a product was already set!
     *
     * @param  Unit $unit
     * @param  Location\Location $location
     * @return int
     */
	public function getStockAfter(Unit $unit, Location\Location $location)
	{
        $this->_checkUnitsSet();

		// throw exception if not set
		return $this->_counter[$unit->id][$location->name];
	}

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        $key = key($this->_movements);
        return ($key !== NULL && $key !== FALSE);
    }

    /**
     * Loads the stock to an array grouped by unitID and then by location-name.
     * Uses _checkUnitsSet-method to make sure a product was already set!
     */
	protected function _loadStockLevels()
	{
        $this->_checkUnitsSet();

		foreach($this->_units as $unit ) {
			foreach($this->_locations->all() as $location) {
				$this->_counter[$unit->id][$location->name] = $unit->getStockForLocation($location);
			}
		}
	}

    /**
     * Checks whether a product was already set.
     *
     * @throws \LogicException  If product has not been set.
     */
    protected function _checkUnitsSet()
    {
        if(is_array($this->_units) && count($this->_units) === 0) {
            throw new \LogicException('To use the movement iterator you first have to add units to iterate over!');
        }
    }

}