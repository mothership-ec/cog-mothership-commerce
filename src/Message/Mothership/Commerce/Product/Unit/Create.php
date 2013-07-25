<?php

namespace Message\Mothership\Commerce\Product\Unit;

use Message\Cog\DB\Query;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Cog\Localisation\Locale;

use Message\User\UserInterface;

class Create
{
	protected $_query;
	protected $_user;
	protected $_locale;

	public function __construct(Query $query, UserInterface $user, Locale $locale)

	{
		$this->_query  = $query;
		$this->_user   = $user;
		$this->_locale = $locale;
	}

	public function save(Unit $unit)
	{
		if (is_null($unit->authorship->createdAt())) {
			$unit->authorship->create(new DateTimeImmutable, $this->_user->id);
		}

		$result = $this->_query->run(
			'INSERT INTO
				product_unit
			SET
				product_id   = :productID?i,
				visible      = :visibile?i,
				barcode      = :barcode?s,
				supplier_ref = :sup_ref?sn,
				weight_grams = :weight?i,
				created_at   = :createdAt?d,
				created_by   = :createdBy?i',
			array(
				'productID' => $unit->product->id,
				'visible'	=> (bool) $unit->visible,
				'barcode'	=> $unit->barcode,
				'sup_ref'	=> $unit->supplierRef,
				'weight'	=> $unit->weightGrams,
				'createdAt' => $unit->authorship->createdAt(),
				'createdBy' => $unit->authorship->createdBy()->id,
				$unit->id
			)
		);

		$unitID = $result->id();

		$this->_query->run(
			'INSERT INTO
				product_unit_info
			SET
				unit_id     = ?i,
				revision_id = 1,
				sku         = ?s',
			array(
				$unitID,
				$unit->sku,
		));

		foreach ($unit->options as $name => $value) {
			$this->_query->run(
				'INSERT INTO
					product_unit_option
				SET
					unit_id      = ?i,
					revision_id  = 1,
					option_name  = ?s,
					option_value = ?s',
				array(
					$unitID,
					$name,
					$value
			));
		}

		$unit->id = $unitID;

		return $unitID ? $unit : false;
	}

	public function savePrices(Unit $unit)
	{
		$options = array();
		$inserts = array();

		foreach ($unit->price as $type => $price) {

			if ($unit->price[$type]->getPrice('GBP', $this->_locale) === 0) {
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
				'INSERT INTO
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

			return $result->affected() ? $unit : false;
		}

		return false;
	}
}