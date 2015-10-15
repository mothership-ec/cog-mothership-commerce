<?php

namespace Message\Mothership\Commerce\Product\Unit;

use Message\Cog\DB\Query;
use Message\Mothership\Commerce\Product\Barcode\CodeGenerator\GeneratorInterface;

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

	public function __construct(Query $query, GeneratorInterface $generator)
	{
		$this->_query = $query;
		$this->_generator = $generator;
	}

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