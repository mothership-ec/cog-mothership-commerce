<?php

namespace Message\Mothership\Commerce\Product\Barcode\CodeGenerator;

use Message\Mothership\Commerce\Product\Unit\Unit;

/**
 * Class AbstractGenerator
 * @package Message\Mothership\Commerce\Product\Barcode\CodeGenerator
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Abstract cass to supply some generic functionality to barcode generators
 */
abstract class AbstractGenerator implements GeneratorInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function generateFromUnit(Unit $unit)
	{
		$this->_validateUnit($unit);

		return (string) ((int) $unit->id);
	}

	/**
	 * Ensure that it is possible to create a barcode from this unit
	 *
	 * @param Unit $unit
	 */
	protected function _validateUnit(Unit $unit)
	{
		if (!$unit->id) {
			throw new \LogicException('Cannot create barcode from a unit that has no ID');
		}
	}
}