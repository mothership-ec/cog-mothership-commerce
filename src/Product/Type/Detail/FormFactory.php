<?php

namespace Message\Mothership\Commerce\Product\Type\Detail;

use Message\Cog\Form\Handler;
use Message\Cog\Validation\Validator;

class FormFactory
{
	/**
	 * @var \Message\Cog\Form\Handler
	 */
	protected $_form;

	/**
	 * @var \Message\Cog\Validation\Validator
	 */
	protected $_validator;

	/**
	 * @var array
	 */
	protected $_fields			= array();

	/**
	 * @var array
	 */
	protected $_defaultValues	= array();

	/**
	 * @var bool
	 */
	protected $_built			= false;

	public function __construct(Handler $form, Validator $validator)
	{
		$this->_form		= $form;
		$this->_validator	= $validator;
	}

	public function add($name, $type = null, $label = null, $options = array())
	{
		$this->_fields[] = array(
			'name'		=> $name,
			'type'		=> $type,
			'label'		=> $label,
			'options'	=> $options,
		);

		return $this->_form;
	}

	public function setDefaultValues(array $defaultValues)
	{
		if ($this->_built) {
			throw new \LogicException('Cannot add default values to a form that has already been built!');
		}

		$this->_defaultValues	= $defaultValues;
	}

	public function getForm()
	{
		if (!$this->_built) {
			$this->_buildForm();
		}

		return $this->_form;
	}

	protected function _buildForm()
	{
		$this->_form->setDefaultValues($this->_defaultValues);
		foreach ($this->_fields as $field) {
			$this->_form->add(
				$field['name'],
				$field['type'],
				$field['label'],
				$field['options']
			);
		}

		$this->_built	= true;

		return $this;
	}
}