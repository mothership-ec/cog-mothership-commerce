<?php

namespace Message\Mothership\Commerce\Field;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints;

class OptionType extends AbstractType {

	protected $_nameLabel;
	protected $_valueLabel;
	protected $_nameChoice = array();
	protected $_valueChoice = array();
	protected $_options;

	public function __construct($nameChoice, $options = array())
	{
		$this->_nameChoice   = $nameChoice;
		$this->_options      = $options;

		return $this;
	}

	public function setValueChoice(array $valueChoice)
	{
		$this->_valueChoice  = $valueChoice;
		return $this;
	}

	public function setNameLabel($nameLabel)
	{
		$this->_nameLabel = $nameLabel;
		return $this;
	}

	public function setValueLabel($valueLabel)
	{
		$this->_valueLabel = $valueLabel;
		return $this;
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$nameHelpKey  = $options['attr']['data-help-key']['name'];
		$valueHelpKey = $options['attr']['data-help-key']['value'];

		if(isset($this->_options['attr']) && isset($this->_options['attr']['data-help-key']) && is_array($this->_options['attr']['data-help-key'])) {
			$dataHelpKey  = $this->_options['attr']['data-help-key'];
    		$nameHelpKey  = (isset($dataHelpKey['name'])  ? $dataHelpKey['name']  : $nameHelpKey);
    		$valueHelpKey = (isset($dataHelpKey['value']) ? $dataHelpKey['value'] : $valueHelpKey);
    	}

		$builder->add('name', 'datalist',
			array(
				'label'    => ($this->_nameLabel ? $this->_nameLabel : 'Name'),
				'choices'  => $this->_nameChoice,
				'required' => true,
				'attr'     => [
					'data-help-key' => $nameHelpKey,
					'placeholder' => "e.g. 'Colour'",
				],
				'constraints' => [
					new Constraints\NotBlank
				]
			)
		);
		if(count($this->_valueChoice) > 0) {
			$builder->add('value', 'choice',
				array(
					'label'    => ($this->_valueLabel ? $this->_valueLabel : 'Value'),
					'choices'  => $this->_valueChoice,
					'required' => true,
					'attr'     => [
						'data-help-key' => $valueHelpKey,
					],
					'constraints' => [
						new Constraints\NotBlank
					]
				)
			);
		} else {
			$builder->add('value', 'text',
				[
					'label'    => ($this->_valueLabel ? $this->_valueLabel : 'Value'),
					'attr'  => [
						'data-help-key' => $valueHelpKey,
						'placeholder' => "e.g. 'Red'",
					],
					'constraints' => [
						new Constraints\NotBlank
					]
				]
			);
		}

		return $builder;
	}

	 /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {

    	$resolver->setDefaults([
            'attr' => [
            	'data-help-key' => [
            		'name'  => '',
            		'value' => ''
            	]
            ]
        ]);
    }

	public function getName()
	{
		return 'option';
	}
}