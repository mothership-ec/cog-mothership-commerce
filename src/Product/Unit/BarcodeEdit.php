<?php

namespace Message\Mothership\Commerce\Product\Unit;

use Message\Cog\DB\Query;
use Message\Mothership\Commerce\Product\Barcode\CodeGenerator\GeneratorInterface;

/**
 * Class BarcodeEdit
 * @package Message\Mothership\Commerce\Product\Unit
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Class for saving barcodes against units using the registered barcode generator.
 */
class BarcodeEdit
{
	/**
	 * @var Query
	 */
	private $_query;

	/**
	 * @var GeneratorInterface
	 */
	private $_generator;

	/**
	 * @param Query $query
	 * @param GeneratorInterface $generator
	 */
	public function __construct(Query $query, GeneratorInterface $generator)
	{
		$this->_query = $query;
		$this->_generator = $generator;
	}

	/**
	 * Generates a barcode with the registered generator and save it to the database
	 *
	 * @param Unit $unit
	 */
	public function generateAndSave(Unit $unit)
	{
		$unit->barcode = $this->_generator->generateFromUnit($unit);

		$this->save($unit);
	}

	/**
	 * Save a barcode the database without generating a new one
	 *
	 * @param Unit $unit
	 */
	public function save(Unit $unit)
	{
		$this->_validateBarcode($unit->barcode);

		$this->_query->run("
			UPDATE
				product_unit
			SET
				barcode = :barcode?s
			WHERE
				unit_id = :id?i
		", [
			'barcode' => $unit->barcode,
			'id'      => $unit->id,
		]);
	}

	/**
	 * Check that the barcode is valid
	 *
	 * @param $barcode
	 */
	private function _validateBarcode($barcode)
	{
		if (!$barcode) {
			throw new \InvalidArgumentException('Barcode cannot be blank');
		}

		if (!is_scalar($barcode)) {
			throw new \InvalidArgumentException('Barcode must be scalar, ' . gettype($barcode) . ' given');
		}
	}

}