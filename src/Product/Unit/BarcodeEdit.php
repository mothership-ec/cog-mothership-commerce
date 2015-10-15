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
}