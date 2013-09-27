<?php

namespace Message\Mothership\Commerce\Field;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OptionType extends AbstractType {

	protected $_nameLabel;
	protected $_valueLabel;
	protected $_choices;

	public function __construct($nameLabel, $valueLabel, array $choices)
	{
		$this->_nameLabel   = $nameLabel;
		$this->_valueLabel  = $valueLabel;

		$this->_choices = $choices;
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('name', 'choice',
			array(
				'label'    => $this->_nameLabel,
				'choices'  => $this->_choices,
				'required' => true,
				'attr'     => array(
					'data-help-key' => 'ms.commerce.product.units.option.type.help'
				),
			)
		);

		$builder->add('value', 'text',
			array(
				'label' => $this->_valueLabel,
				'attr'  => array(
					'data-help-key' => 'ms.commerce.product.units.option.value.help',
				)
			)
		);

		return $builder;
	}

	public function getName()
	{
		return 'option';
	}
}