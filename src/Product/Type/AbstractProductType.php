<?php

namespace Message\Mothership\Commerce\Product\Type;

use Message\Cog\Form\Handler;
use Message\Cog\Service\ContainerAwareInterface;
use Message\Cog\Service\ContainerInterface;
use Message\Mothership\Commerce\Product\Product;

abstract class AbstractProductType implements ProductTypeInterface, \Countable, ContainerAwareInterface
{
	/**
	 * @var ContainerInterface
	 */
	protected $_services;

	/**
	 * @var Product
	 */
	protected $_product;

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
		$this->setFields();
	}

	public function setProduct(Product $product)
	{
		$this->_product	= $product;

		return $this;
	}

	public function setDetailsForm(Handler $form)
	{
		$this->_detailsForm		= $form;

		return $this;
	}

	public function setAttributesForm(Handler $form)
	{
		$this->_attributesForm	= $form;

		return $this;
	}

	public function getDetailsForm()
	{
		return $this->_detailsForm;
	}

	public function getAttributesForm()
	{
		if (!$this->_product) {
			throw new \LogicException('Product not set');
		}
		elseif (!$this->_attributesForm) {
			$this->setAttributesForm($this->_services['product.form.attributes']->build($this->_product));
		}

		return $this->_attributesForm;
	}

	public function add($name, $type = null, $label = null, $options = array())
	{
		$this->_detailsForm->add($name, $type, $label, $options);
		$this->_details[$label]	= $label;

		return $this->_detailsForm;
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