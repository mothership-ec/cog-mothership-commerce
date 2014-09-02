<?php

namespace Message\Mothership\Commerce\Product\Type;

use Message\Cog\Field;
use Message\Cog\Validation\Validator;
use Message\Cog\ValueObject\Collection as BaseCollection;

class DetailCollection extends BaseCollection implements \IteratorAggregate, \Countable
{
	public function __construct($details = array())
	{
		foreach ($details as $detail) {
			if (!$detail instanceof Field\FieldInterface) {
				throw new \LogicException('Objects passed to Details must be an instance of Field\FieldInterface');
			}
			$this->add($detail->name, $detail);
		}

		$this->setSort(function($a, $b) {
			return 0;
		});

		$this->setKey(function($var) {
			return $var->getName();
		});
	}

	public function __set($var, Field\FieldInterface $value)
	{
		if($this->exists($var)) {
			$this->remove($var);
		}

		$this->add($value);
	}

	public function __get($key)
	{
		// if detail not yet set, Collection will throw Exception.
		// return null instead to preserve array like properties for BC.
		if($this->exists($key)) {
			return $this->get($key);
		}

		return null;
	}
}