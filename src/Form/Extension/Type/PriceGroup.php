<?php

namespace Message\Mothership\Commerce\Form\Extension\Type;

use Symfony\Component\Form;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;

class PriceGroup extends Form\AbstractType
{
	protected $_priceTypes;

	public function __construct(array $priceTypes)
	{
		$this->_priceTypes = $priceTypes;
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$priceTypes = $options['price_types'];
		$currency   = $options['currency'];

		foreach ($priceTypes as $type => $value) {
			$value = $value?:0;
			$builder->add($type, 'money', ['currency' => $currency]);
		}
	}

	/**
	 * {@inheritDoc}
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{

		$resolver->setRequired([
			'currency',
		]);

		$resolver->setDefaults([
			'price_types' => array_combine($this->_priceTypes, $this->_priceTypes),
		]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return 'price_group';
	}
}