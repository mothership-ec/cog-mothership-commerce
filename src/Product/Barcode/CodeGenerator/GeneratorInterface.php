<?php

namespace Message\Mothership\Commerce\Product\Barcode\CodeGenerator;

use Message\Mothership\Commerce\Product\Unit\Unit;

/**
 * Interface GeneratorInterface
 * @package Message\Mothership\Commerce\Product\Barcode\Code
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Interface representing a class that can generate barcodes for units
 */
interface GeneratorInterface
{
	/**
	 * Get the name of the generator. This is the primary identifier for the generator and must be unique
	 * with the collection.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Get the type of barcode that this generator creates. This must be one of the types declared in
	 * Message\Mothership\Commerce\Product\Barcode\ValidTypes. This can also be used a secondary identifier
	 * for the generator, if the name is not explicitly declared in the config, for instance.
	 *
	 * @return string
	 */
	public function getBarcodeType();

	/**
	 * Generate a barcode form a unit. This method should always return a valid barcode for its given type. For
	 * instance, EAN 13 barcodes must always be 13 characters long and end with a valid check digit.
	 *
	 * @param Unit $unit
	 *
	 * @return string
	 */
	public function generateFromUnit(Unit $unit);
}