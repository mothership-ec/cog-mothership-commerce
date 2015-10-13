<?php

namespace Message\Mothership\Commerce\Constraint\Product;

use Symfony\Component\Validator\Constraint;

class UnitHasOptions extends Constraint
{
	public $message = 'Cannot create unit as it must have at least one option';
}