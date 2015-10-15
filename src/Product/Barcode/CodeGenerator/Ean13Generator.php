<?php

namespace Message\Mothership\Commerce\Product\Barcode\CodeGenerator;

use Message\Mothership\Commerce\Product\Unit\Unit;
use Image_Barcode2 as ImageBarcode;

/**
 * Class Ean13Generator
 * @package Message\Mothership\Commerce\Product\Barcode\CodeGenerator
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Class for generating EAN13 type barcodes. These barcodes must include a 'check digit' as the final
 * digit of the barcode. This tells barcode scanners that it has reached the end of the barcode. It is calculated
 * by splitting out each digit, multiplying every second digit by three, and then adding them together. Once
 * all the digits have been added together, take the last digit off that total, and subtract it from 10.
 *
 * Note: This class merely takes a prefix number (which typically represents the region), and the unit ID, and then
 * adds padding between the two. It does *not* currently take into account the manufacturer code.
 */
class Ean13Generator extends AbstractGenerator
{
	const LENGTH = 13;

	/**
	 * @var int | string
	 */
	private $_prefixNumber;

	/**
	 * @var int | string
	 */
	private $_paddingNumber;

	/**
	 * @param int | string $prefixNumber
	 * @param int | string $paddingNumber
	 */
	public function __construct($prefixNumber = 50, $paddingNumber = 0)
	{
		$this->setPrefixNumber($prefixNumber);
		$this->setPaddingNumber($paddingNumber);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return ImageBarcode::BARCODE_EAN13;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getBarcodeType()
	{
		return ImageBarcode::BARCODE_EAN13;
	}

	/**
	 * {@inheritDoc}
	 */
	public function generateFromUnit(Unit $unit)
	{
		$this->_validateUnit($unit);

		$prefix = (string) $this->_prefixNumber;

		$padding = self::LENGTH - 1 - strlen($prefix);

		$barcode = $prefix . str_pad($unit->id, $padding, $this->_paddingNumber, STR_PAD_LEFT);
		$total = 0;

		if (strlen($barcode) + 1 > self::LENGTH) {
			throw new Exception\BarcodeGenerationException('Could not create barcode, as the combination of the prefix number and the unit ID are too long');
		}

		foreach (str_split($barcode) as $i => $char) {
			if ($i % 2 !== 0) {
				$weighting = 3;
			} else {
				$weighting = 1;
			}
			$char = (int) $char;
			$total += $weighting * $char;
		}

		$small = $total % 10;
		$barcode = $barcode . ((string) (10 - $small) % 10);

		return $barcode;
	}

	/**
	 * Set the number that appears at the start of the barcode, typically determined by the country
	 *
	 * @param int | string $prefixNumber
	 * @throws \InvalidArgumentException   Throws exception if prefix number is not numeric
	 * @throws \InvalidArgumentException   Throws exception if prefix number is not a whole number
	 */
	public function setPrefixNumber($prefixNumber)
	{
		if (!is_numeric($prefixNumber)) {
			throw new \InvalidArgumentException('Prefix number must be numeric');
		}

		if ((int) $prefixNumber != $prefixNumber) {
			throw new \InvalidArgumentException('Prefix number must be a whole number');
		}

		$this->_prefixNumber = $prefixNumber;
	}

	/**
	 * Get the prefix number
	 *
	 * @return int | string
	 */
	public function getPrefixNumber()
	{
		return $this->_prefixNumber;
	}

	/**
	 * Set the number that will be used to pad the space between the prefix number and the unit ID
	 *
	 * @param int | string $paddingNumber
	 * @throws \InvalidArgumentException    Throws exception if padding number is not numeric
	 * @throws \InvalidArgumentException    Throws exception if padding number is not a whole number
	 * @throws \LogicException              Throws exception if padding number is more than one digit
	 */
	public function setPaddingNumber($paddingNumber)
	{
		if (!is_numeric($paddingNumber)) {
			throw new \InvalidArgumentException('Padding number must be numeric');
		}

		if ((int) $paddingNumber != $paddingNumber) {
			throw new \InvalidArgumentException('Prefix number must be a whole number');
		}

		if (strlen((string) $paddingNumber) !== 1) {
			throw new \LogicException('Padding number may only be one digit');
		}

		$this->_paddingNumber = $paddingNumber;
	}

	/**
	 * Get the padding number
	 *
	 * @return int | string
	 */
	public function getPaddingNumber()
	{
		return $this->_paddingNumber;
	}
}