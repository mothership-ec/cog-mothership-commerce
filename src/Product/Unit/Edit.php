<?php

namespace Message\Mothership\Commerce\Product\Unit;

use Message\Cog\Localisation\Locale;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Mothership\Commerce\Product\Price\Pricing;
use Message\Mothership\Commerce\Product\Stock\Location\Location;

use Message\Cog\DB;

use Message\User\UserInterface;

class Edit implements DB\TransactionalInterface
{
	protected $_query;
	protected $_loader;
	protected $_user;
	protected $_locale;

	public function __construct(DB\Query $query, Loader $loader, UserInterface $user, Locale $locale)
	{
		$this->_query  = $query;
		$this->_loader = $loader;
		$this->_user   = $user;
		$this->_locale = $locale;
	}

	public function setTransaction(DB\Transaction $trans)
	{
		$this->_query = $trans;
	}

	public function save(Unit $unit)
	{
		$currentUnit = $this->_loader->includeInvisible(true)->includeOutOfStock(true)->getByID($unit->id, $unit->revisionID, $unit->product);
		$unit->authorship->update(new DateTimeImmutable, $this->_user->id);

		$result = $this->_query->run(
			'UPDATE
				product_unit
			SET
				visible      = ?b,
				supplier_ref = ?s,
				weight_grams = ?in,
				updated_at   = ?d,
				updated_by   = ?in
			WHERE
				unit_id 	 = ?i
			', 	array(
					(bool) $unit->visible,
					$unit->supplierRef,
					$unit->weight,
					$unit->authorship->updatedAt(),
					$unit->authorship->updatedBy(),
					$unit->id
			)
		);


		if ($currentUnit->options != $unit->options || $currentUnit->sku != $unit->sku) {
			$options = array();
			$inserts = array();
			$newRevisionID = $unit->revisionID + 1;
			foreach ($unit->options as $optionName => $optionValue) {
				$options[] = $unit->id;
				$options[] = strtolower($optionName);
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

			$updateInfo = $this->_query->run(
				'REPLACE INTO
					product_unit_info
					(
						unit_id,
						revision_id,
						sku
					)
				VALUES
					(
						?i,
						?i,
						?s
					)',
				array(
					$unit->id,
					$newRevisionID,
					$unit->sku,
			));
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
			$currencies = $price->getCurrencies();
			foreach($currencies as $currency) {
				$unitPrice    = $unit->price[$type]->getPrice($currency, $this->_locale);
				$productPrice = $unit->product->getPrices()[$type]->getPrice($currency, $this->_locale);

				$unitPrice    = $unit->price[$type]->getPrice($currency, $this->_locale);
				$productPrice = $unit->product->getPrices()[$type]->getPrice($currency, $this->_locale);

				// If the unit price is equal to the product price then we don't
				// need to add a row, and same if the price is zero
				if ($unitPrice === 0 || $unitPrice == $productPrice ) {
					continue;
				}

				$options[] = $unit->id;
				$options[] = $type;
				$options[] = $unit->price[$type]->getPrice($currency, $this->_locale);
				$options[] = $currency;
				$options[] = $this->_locale->getID();
				$inserts[] = '(?i,?s,?s,?s,?s)';
			}
		}

		if ($options) {
			$result = $this->_query->run(
				// replace into won't work
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

	public function saveStock(Unit $unit)
	{
		$currentUnit = $this->_loader->includeInvisible(true)->includeOutOfStock(true)->getByID($unit->id, $unit->product);

		foreach($unit->stock as $location => $stock) {
			// just update it if the stock-level has actually changed
			if(!array_key_exists($location, $unit->stock) || $stock != $unit->stock[$location]) {
				$this->_saveStockLevel($unit->id, $location, $stock);
			}
		}
	}

	public function saveStockForLocation(Unit $unit, Location $location)
	{
		return $this->_saveStockLevel($unit->id, $location->name, $unit->getStockForLocation($location));
	}

	protected function _saveStockLevel($unit_id, $location, $stock)
	{
		$result = $this->_query->run(
			'REPLACE INTO
				product_unit_stock
				(
					unit_id,
					location,
					stock
				)
			VALUES
				(
					?i,
					?s,
					?i
				)',
			array(
				$unit_id,
				$location,
				$stock,
			)
		);
	}
}