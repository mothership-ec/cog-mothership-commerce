<?php

namespace Message\Mothership\Commerce\Constraint\Product;

use Symfony\Component\Validator\Constraint;

/**
 * Class UnitHasOptions
 * @package Message\Mothership\Commerce\Constraint\Product
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Constraint to be applied when adding units. If a unit has no options the form should invalidate the
 * data
 */
class UnitHasOptions extends Constraint
{
	public $message = 'Cannot create unit as it must have at least one option';
}