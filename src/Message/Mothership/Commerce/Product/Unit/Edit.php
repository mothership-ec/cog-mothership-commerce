<?php

namespace Message\Mothership\Commerce\Product\Unit;

use Message\Cog\Localisation\Locale;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Mothership\Commerce\Product\Pricing;

use Message\Cog\DB\Query;
use Message\Cog\DB\Result;

use Message\User\UserInterface;

class Edit
{
	protected $_query;
	protected $_loader;
	protected $_user;
	protected $_locale;

	public function __construct(Query $query, Loader $loader, UserInterface $user, Locale $locale)
	{
		$this->_query  = $query;
		$this->_loader = $loader;
		$this->_user   = $user;
		$this->_locale = $locale;
	}

	public function save(Unit $unit)
	{
		$currentUnit = $this->_loader->includeInvisible(true)->includeOutOfStock(true)->getByID($unit->id, $unit->product);
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
				'REPLACE INTO
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

	public function savePrices(Unit $unit)
	{

		$result = $this->_query->run(
			'DELETE FROM
				product_unit_price
			WHERE
				unit_id = ?i',
			array(
				$unit->id,
			)
		);

		$options = array();
		$inserts = array();

		foreach ($unit->price as $type => $price) {
			$unitPrice = $unit->price[$type]->getPrice('GBP', $this->_locale);
			$productPrice = $unit->product->price[$type]->getPrice('GBP', $this->_locale);

			// If the unit price is equal to the product price then we don't
			// need to add a row, and same if the price is zero
			if ($unitPrice === 0 || $unitPrice == $productPrice ) {
				continue;
			}

			$options[] = $unit->id;
			$options[] = $type;
			$options[] = $unit->price[$type]->getPrice('GBP', $this->_locale);
			$options[] = 'GBP';
			$options[] = $this->_locale->getID();
			$inserts[] = '(?i,?s,?s,?s,?s)';
		}

		if ($options) {
			$result = $this->_query->run(
				'REPLACE INTO
					product_unit_price
					(
						unit_id,
						type,
						price,
						currency_id,
						locale
					)
				VALUES
					'.implode(',',$inserts).' ',
				$options
			);
		}

		return $unit;
	}
}