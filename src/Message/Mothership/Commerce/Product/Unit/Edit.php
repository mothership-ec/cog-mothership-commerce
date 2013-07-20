<?php

namespace Message\Mothership\Commerce\Product\Unit;

use Message\Cog\Localisation\Locale;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Mothership\Commerce\Product\Pricing;

use Message\Cog\DB\Query;
use Message\Cog\DB\Result;

use Message\User\User;

class Edit
{
	protected $_query;
	protected $_user;

	public function __construct(Query $query, User $user)
	{
		$this->_query = $query;
		$this->_user  = $user;
	}

	public function save(Unit $unit)
	{
		$unit->authorship->update(new DateTimeImmutable, $this->_user->id);

		$result = $this->_query->run(
			'UPDATE
				product_unit
			SET
				visible      = ?i,
				supplier_ref = ?s,
				weight_grams = ?i,
				updated_at   = ?d,
				updated_by   = ?i
			WHERE
				unit_id 	 = ?i
			', 	array(
					(bool) $unit->visible,
					$unit->supplierRef,
					$unit->weightGrams,
					$unit->authorship->updatedAt(),
					$unit->authorship->updatedBy(),
					$unit->id
			)
		);

		$options = array();
		$inserts = array();
		foreach ($unit->options as $optionName => $optionValue) {
			$options[] = $unit->id;
			$options[] = $optionName;
			$options[] = $optionValue;
			$inserts[] = '(?i,?s,?s)';
		}

		$optionUpdate = $this->_query->run(
			'REPLACE INTO
				product_unit_option
				(
					unit_id,
					option_name,
					option_value
				)
			VALUES
			'.implode(',',$inserts).'',
				$options
		);

		return $result->affected() ? $unit : false;
	}
}