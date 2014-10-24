<?php

namespace Message\Mothership\Commerce\Product\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UnitEdit extends AbstractType
{
	protected $_currencies;

	public function __construct(array $currencies)
	{
		$this->_currencies = $currencies;
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$units   = $options['unit']; 

		$unitsForm = $builder->create('units', 'form');
		foreach ($units as $unit) {
			$unitForm = $builder->create($unit->id, 'form');

			$unitForm->add('sku', 'text', [
				'label' => false,
				'data'  => $unit->sku,
				'attr'  => ['placeholder' => 'ms.commerce.product.units.sku.placeholder'],
			])

			$unitsForm->add($unitForm);
		}

	}

	/**
	 * Sets the default currency and tax rate options, product is required
	 *
	 * {@inheritDoc}
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setRequired([
			'units',
		]);

		$resolver->setOptional([
			'locale',
		]);

		$resolver->setDefaults([
			'currencies' => $this->_currencies,
			'tax_rate'   => $this->_taxRates,
			'locale'     => null,
		]);
	}

	public function getName()
	{
		return 'unit_edit';
	}
}