<?php

namespace Message\Mothership\Commerce\Constraint\Product;

use Symfony\Component\Validator;

class UnitHasOptionsValidator extends Validator\ConstraintValidator
{
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