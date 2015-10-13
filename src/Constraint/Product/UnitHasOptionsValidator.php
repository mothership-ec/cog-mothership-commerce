<?php

namespace Message\Mothership\Commerce\Constraint\Product;

use Symfony\Component\Validator;

/**
 * Class UnitHasOptionsValidator
 * @package Message\Mothership\Commerce\Constraint\Product
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Class that checks if a unit has options set, and adds an error message if not
 */
class UnitHasOptionsValidator extends Validator\ConstraintValidator
{
	/**
	 * {@inheritDoc}
	 * @throws \InvalidArgumentException       Throws exception if $constraint is not an instance of UnitHasOptions
	 * @throws \InvalidArgumentException       Throws exception if $value is not an array
	 */
	public function validate($value, Validator\Constraint $constraint)
	{
		if (!$constraint instanceof UnitHasOptions) {
			throw new \InvalidArgumentException('Constraint must be an instance of `' . __NAMESPACE__ . '\\UnitHasOptions`, `' . get_class($constraint) . '` given');
		}

		if (!is_array($value)) {
			throw new \InvalidArgumentException('Value must be an array, ' . gettype($value) . ' given');
		}

		if (count($value) <= 0) {
			$this->context->addViolation($constraint->message);
		}
	}
}