<?php

namespace Message\Mothership\Commerce\Product\Unit;

use Message\Cog\DB\Transaction;
use Message\Cog\ValueObject\DateTimeImmutable;

use Message\User\User;

class Create
{
	protected $_query;
	protected $_user;

	public function __construct(Transaction $query, User $user)
	{
		$this->_query 	= $query;
		$this->_user	= $user;
	}

	public function save(Unit $unit)
	{
		if (is_null($unit->authorship->createdAt())) {
			$unit->authorship->create(new DateTimeImmutable, $this->_user->id);
		}

		$this->_query->add(
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
				'visible'	=> $unit->visible,
				'barcode'	=> $unit->barcode,
				'sup_ref'	=> $unit->supplierRef,
				'weight'	=> $unit->weightGrams,
				'createdAt' => $unit->authorship->createdAt(),
				'createdBy' => $unit->authorship->createdBy(),
				$unit->id
			)
		);
		$this->_query->add('SET @UNIT_ID = LAST_INSERT_ID();');
		$this->_query->add(
			'INSERT INTO
				product_unit_info
			SET
				unit_id     = @UNIT_ID,
				revision_id = 1,
				sku         = ?s',
			array(
				$unit->sku,
		));

		foreach ($unit->options as $name => $value) {
			$this->_query->add(
				'INSERT INTO
					product_unit_option
				SET
					unit_id      = @UNIT_ID,
					revision_id  = 1,
					option_name  = ?s,
					option_value = ?s',
				array(
					$name,
					$value
			));
		}

		return $this->_query->commit() ? $unit : false;
	}
}