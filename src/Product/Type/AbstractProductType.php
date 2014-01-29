<?php

namespace Message\Mothership\Commerce\Product\Type;

use Message\Cog\Form\Handler;
use Message\Cog\Service\ContainerAwareInterface;
use Message\Cog\Service\ContainerInterface;

abstract class AbstractProductType implements ProductTypeInterface, \Countable, ContainerAwareInterface
{
	/**
	 * @var ContainerInterface
	 */
	protected $_services;

	/**
	 * @var Handler
	 */
	protected $_attributesForm;

	/**
	 * @var Handler
	 */
	protected $_detailsForm;

	/**
	 * @var array
	 */
	protected $_details		= array();

	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
		$this->setDetailsForm($container['form']);
		$this->setAttributesForm($container['product.form.attributes']->build());
		$this->setFields();
	}

	public function setDetailsForm(Handler $form)
	{
		$this->_detailsForm		= $form;
	}

	public function setAttributesForm(Handler $form)
	{
		$this->_attributesForm	= $form;
	}

	public function add($name, $type = null, $label = null, $options = array())
	{
		$this->_form->add($name, $type, $label, $options);
		$this->_details[$label]	= $label;

		return $this->_form;
	}

	public function count()
	{
		return count($this->_details);
	}

	public function setContainer(ContainerInterface $container)
	{
		$this->_services	= $container;
	}

	public function trans($message, array $params = array(), $domain = null, $locale = null)
	{
		return $this->_services['translator']->trans($message, $params, $domain, $locale);
	}
}