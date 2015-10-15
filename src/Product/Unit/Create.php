<?php

namespace Message\Mothership\Commerce\Product\Unit;

use Message\Cog\DB\Query;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Cog\Localisation\Locale;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Message\User\UserInterface;

class Create
{
	protected $_query;
	protected $_user;
	protected $_locale;
	protected $_dispatcher;

	public function __construct(Query $query, UserInterface $user, Locale $locale, EventDispatcher $dispatcher)
	{
		$this->_query       = $query;
		$this->_user        = $user;
		$this->_locale      = $locale;
		$this->_dispatcher  = $dispatcher;
	}

	public function save(Unit $unit)
	{
		return $this->create($unit);
	}

	public function create(Unit $unit)
	{
		if (count($unit->options) === 0) {
			throw new \LogicException('Cannot create a unit as it has no options!');
		}

		$event = new Event($unit);
		$this->_dispatcher->dispatch(Events::PRODUCT_UNIT_BEFORE_CREATE, $event);

		if (!$unit->authorship->createdAt()) {
			$unit->authorship->create(new DateTimeImmutable, $this->_user->id);
		}

		$result = $this->_query->run("
			INSERT INTO
				product_unit
			SET
				product_id   = :productID?i,
				visible      = :visible?i,
				barcode      = :barcode?sn,
				supplier_ref = :sup_ref?sn,
				weight_grams = :weight?i,
				created_at   = :createdAt?d,
				created_by   = :createdBy?i
		", [
			'productID' => $unit->product->id,
			'visible'	=> (bool) $unit->visible,
			'barcode'	=> $unit->barcode,
			'sup_ref'	=> $unit->supplierRef,
			'weight'	=> $unit->weight,
			'createdAt' => $unit->authorship->createdAt(),
			'createdBy' => $unit->authorship->createdBy()->id,
		]);

		$unitID = $result->id();
		$unit->sku = $unit->sku ?: $unitID;

		$this->_query->run(
			'INSERT INTO
				product_unit_info
			SET
				unit_id     = :unitID?i,
				revision_id = 1,
				sku         = :sku?s',
			array(
				'unitID' => $unitID,
				'sku'    => $unit->sku ?: $unitID,
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
					strtolower($name),
					$value
			));
		}

		$unit->id = $unitID;

		$this->_savePrices($unit);

		$event = new Event($unit);
		$this->_dispatcher->dispatch(Events::PRODUCT_UNIT_AFTER_CREATE, $event);
		return $unit;
	}

	protected function _savePrices(Unit $unit)
	{
		$options = array();
		$inserts = array();

		foreach ($unit->price as $type => $price) {
			$currencies = $price->getCurrencies();

			foreach($currencies as $currency) {

				$unitPrice    = $unit->price[$type]->getPrice($currency, $this->_locale);
				$productPrice = $unit->product->getPrices()[$type]->getPrice($currency, $this->_locale);

				// If the unit price is equal to the product price then we don't
				// need to add a row, and same if the price is zero
				if ($unitPrice === 0 || $unitPrice === null || $unitPrice == $productPrice ) {
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