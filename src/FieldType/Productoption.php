<?php

namespace Message\Mothership\Commerce\FieldType;

use Message\Cog\Field;

use Message\Mothership\Commerce\Product\OptionLoader;

use Message\Cog\Form\Extension\Type\LinkedChoice;
use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Validator\Constraints;

/**
 * A field for a link to an internal or external page.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Productoption extends Field\MultipleValueField
{
	protected $_loader;

	public function __construct(OptionLoader $loader)
	{
		$this->_loader = $loader;
	}


	public function getFieldType()
	{
		return 'productoption';
	}

	public function getFormType()
	{
		$names  = $this->_loader->getAllOptionNames();
		$values = $this->_loader->getAllOptionValues();
		$field  = new LinkedChoice(array(
			'name'  => array_combine($names, $names),
			'value' => array_combine($values, $values),
		));

		return $field;
	}

	public function getFormField(FormBuilder $form)
	{
		$field = $this->getFormType();

		$this->_options['constraints'] = [
			new Constraints\NotBlank,
		];

		$form->add($this->getName(), $field, $this->getFieldOptions());
	}

	/**
	 * {@inheritdoc}
	 */
	public function getValueKeys()
	{
		return array(
			'name',
			'value',
		);
	}
}