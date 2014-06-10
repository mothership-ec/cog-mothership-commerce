<?php

namespace Message\Mothership\Commerce\Product\Stock\Notification\Replenished;

use LogicException;
use Symfony\Component\Form;
use Message\Mothership\Commerce\Product\Product;

class SignupForm extends Form\AbstractType
{
	public function getName()
	{
		return 'replenished_notification';
	}

	public function buildForm(Form\FormBuilderInterface $builder, array $options)
	{
		extract($options['data']);

		$fullyOutOfStock = count($units) === count($oosUnits);

		$choices = [];

		if ($fullyOutOfStock and ($collapseFullyOutOfStock or 1 == count($units))) {
			foreach ($units as $unit) {
				$choices[$unit->id] = implode(',', $unit->options);
			}

			$builder->add('product_units', 'collection', [
				'type' => 'hidden',
				'label' => ' ',
				'data' => array_keys($choices)
			]);
		}
		else {
			foreach ($oosUnits as $unit) {
				$choices[$unit->id] = implode(',', $unit->options);
			}

			// Don't set this field name to `units`, it will bug out. No idea.
			$builder->add('product_units', 'choice', [
				'label' => 'Choose the options you are interested in',
				'choices' => $choices,
				'expanded' => true,
				'multiple' => true
			]);
		}

		$builder->add('email', 'text', [
			'label' => 'Email',
			'data' => $email,
		]);
	}
}