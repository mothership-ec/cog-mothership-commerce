<?php

namespace Message\Mothership\Commerce\Product\Barcode\CodeGenerator;

use Message\Mothership\Commerce\Product\Unit\Unit;

abstract class AbstractGenerator implements GeneratorInterface
{
	public function generateFromUnit(Unit $unit)
	{
		$this->_validateUnit($unit);

		return $unit->id;
	}

	protected function _validateUnit(Unit $unit)
	{
		if (!$unit->id) {
			throw new \LogicException('Cannot create barcode from a unit that has no ID');
		}
	}
}