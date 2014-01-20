<?php

namespace Message\Mothership\Commerce\Product\Stock\Movement\Reason;

class Reason
{
	public $name;
	public $description;

	/**
	 * Initiate the object and set name and description
	 *
	 * @param string $name     		Short name for the reason
	 * @param string $displayName   Description or exact name for the reason
	 */
	public function __construct($name, $description = '')
	{
		$this->name = $name;

		if($description === '') {
			$description = $this->name;
		}

		$this->description = $description;
	}

	public function __toString()
	{
		return $this->description;
	}
}