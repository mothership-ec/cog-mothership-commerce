<?php

namespace Message\Mothership\Commerce\Product\Barcode\CodeGenerator;

use Message\Mothership\Commerce\Product\Unit\Unit;

/**
 * Interface GeneratorInterface
 * @package Message\Mothership\Commerce\Product\Barcode\Code
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 */
interface GeneratorInterface
{
	public function getName();
	public function getBarcodeType();
	public function generateFromUnit(Unit $unit);
}