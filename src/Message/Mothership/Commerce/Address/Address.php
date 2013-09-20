<?php

namespace Message\Mothership\Commerce\Address;

use Message\Cog\ValueObject\Authorship;

class Address
{
	const DELIVERY = 'delivery';
	const BILLING  = 'billing';
	const AMOUNT_LINES = 4;

	public $id;
	public $type;
	public $lines;
	public $town;
	public $stateID;
	public $state;
	public $postcode;
	public $country;
	public $countryID;
	public $telephone;

	public function __construct()
	{
		for($i = 1; $i <= self::AMOUNT_LINES; ++$i) {
			$lines[$i] = null;
		}
	}

	public function setLines(array $lines)
	{
		if (count($lines) > self::AMOUNT_LINES) {
			throw new \InvalidArgumentException(sprintf('An Address can only have %d lines, `%s` passed', self::AMOUNT_LINES, count($lines)));
		}

		$i = 1;

		foreach ($lines as $line) {
			$this->lines[$i] = $line;

			$i++;
		}
	}

	/**
	 * Flatten the address into a single array. Any falsey values are not added
	 * as elements. This is handy for showing the address with line breaks.
	 *
	 * The following fields are included:
	 *
	 * * All lines
	 * * Town
	 * * State
	 * * Postcode
	 * * Country
	 * * Telephone
	 *
	 * @return [type] [description]
	 */
	public function flatten()
	{
		$return = array();

		foreach ($this->lines as $line) {
			if ($line) {
				$return[] = $line;
			}
		}

		if ($this->town) {
			$return[] = $this->town;
		}

		if ($this->state) {
			$return[] = $this->state;
		}

		if ($this->postcode) {
			$return[] = $this->postcode;
		}

		if ($this->country) {
			$return[] = $this->country;
		}

		if ($this->telephone) {
			$return[] = $this->telephone;
		}

		return $return;
	}
}