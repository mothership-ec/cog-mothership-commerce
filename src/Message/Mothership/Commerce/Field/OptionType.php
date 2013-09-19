<?php

namespace Message\Mothership\Commerce\Field;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class OptionType extends AbstractType {

	protected $_labels;
	protected $_choices;

	public function __construct(array $labels, array $choices)
	{
		$this->_label   = $labels;
		$this->_choices = $choices;
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add(
			'option_name',
			'choice',
			$this->_labels['option_name'],
			array(
				'choices' => $this->_choices['option_name'],
				'attr' => array('data-help-key' => 'ms.commerce.product.image.option.name.help'),
			)
		);

		$builder->add(
			'option_value',
			'choice',
			$this->_labels['option_value'],
			array(
				'choices' => $this->_choices['option_value'],
				'attr' => array('data-help-key' => 'ms.commerce.product.image.option.value.help'),
			)
		);

		return $builder;
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'allow_add' => true,
			'allow_delete' => true,
		));
	}

	public function getName()
	{
		return 'option';
	}
}