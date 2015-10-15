<?php

namespace Message\Mothership\Commerce\Product\Barcode\CodeGenerator;

use Message\Mothership\Commerce\Product\Unit\Unit;
use Image_Barcode2 as ImageBarcode;

class Ean13Generator extends AbstractGenerator
{
	const LENGTH = 13;

	private $_prefixNumber;
	private $_paddingNumber;

	public function __construct($prefixNumber = 50, $paddingNumber = 0)
	{
		$this->setPrefixNumber($prefixNumber);
		$this->setPaddingNumber($paddingNumber);
	}

	public function getName()
	{
		return ImageBarcode::BARCODE_EAN13;
	}

	public function getBarcodeType()
	{
		return ImageBarcode::BARCODE_EAN13;
	}

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

	public function getPrefixNumber()
	{
		return $this->_prefixNumber;
	}

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

	public function getPaddingNumber()
	{
		return $this->_paddingNumber;
	}
}