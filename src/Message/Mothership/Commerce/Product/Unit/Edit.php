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
	protected $_loader;
	protected $_user;

	public function __construct(Query $query, Loader $loader, User $user)
	{
		$this->_query  = $query;
		$this->_loader = $loader;
		$this->_user   = $user;
	}

	public function save(Unit $unit)
	{
		$currentUnit = $this->_loader->getByID($unit->id, $unit->product);

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

		if ($currentUnit->options != $unit->options) {
			$options = array();
			$inserts = array();
			$newRevisionID = $unit->revisionID + 1;
			foreach ($unit->options as $optionName => $optionValue) {
				$options[] = $unit->id;
				$options[] = $optionName;
				$options[] = $optionValue;
				$options[] = $newRevisionID;
				$inserts[] = '(?i,?s,?s,?i)';
			}

			$optionUpdate = $this->_query->run(
				'INSERT INTO
					product_unit_option
					(
						unit_id,
						option_name,
						option_value,
						revision_id
					)
				VALUES
				'.implode(',',$inserts).'',
					$options
			);
		}

		return $result->affected() ? $unit : false;
	}
}