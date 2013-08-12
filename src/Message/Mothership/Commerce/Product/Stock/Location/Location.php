<?php

namespace Message\Mothership\Commerce\Product\Stock;

class Location
{
	public $name;
	public $displayName;

	/**
	 * Initiate the object and set name and displayName
	 *
	 * @param string $name     		Short name for location
	 * @param string $displayName   Actual name displayed
	 */
	public function __construct($name, $displayName = '')
	{
		$this->name = $name;

		if($displayName === '') {
			$displayName = $this->name;
		}

		$this->displayName = $displayName;
	}

	public function __toString()
	{
		return sprintf('(%s) %s', $this->name, $this->displayName);
	}
}