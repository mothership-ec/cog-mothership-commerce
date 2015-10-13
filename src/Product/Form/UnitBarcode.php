<?php

namespace Message\Mothership\Commerce\Product\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UnitBarcode extends AbstractType
{
	/**
	 * {@inheritDoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('unit', 'hidden', ['required' => true]);

		$builder->add('barcode', 'number', [
			'attr' => [
				'data-help-key' => 'ms.commerce.product.units.barcode.help'
			],
			'required' => true
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return 'unit_barcode';
	}
}