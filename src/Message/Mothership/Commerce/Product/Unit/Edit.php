<?php

namespace Message\Mothership\Commerce\Product\Unit;

use Message\Cog\Localisation\Locale;
use Message\Mothership\Commerce\Product\Pricing;

class Edit
{

	public function __construct(Query $query)
	{
		$this->_query = $query;
	}

	public function save(Unit $unit)
	{
		$result = $this->_query->run(
			'UPDATE
				product_unit
			SET
				visible = ?i,
				supplier_ref = ?s,
				weight_grams = ?i
			WHERE
				unit_id = ?i
			', 	array(
					(bool) $unit->visible,
					$unit->supplierRef,
					$unit->weightGrams,
					$unit->id
			)
		);

		return $return->affected() ? $unit : false;
	}
}