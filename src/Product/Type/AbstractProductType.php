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
	protected $_detailForm;

	/**
	 * @var array
	 */
	public $_details	= array();

	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
	}

	public function setProduct(Product $product)
	{
		$this->_product	= $product;

		return $this;
	}

	public function setAttributesForm(Handler $form)
	{
		$this->_attributesForm	= $form;

		return $this;
	}

	public function getDetailsForm()
	{
		if (!$this->_detailForm) {
			$this->_createForm();
		}
		return $this->_detailForm;
	}

	public function setDetails(Detail\Collection $details)
	{
		$this->_details	= $details;
	}

	public function getDetails()
	{
		return $this->_details;
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
		if (!$this->_detailForm) {
			$this->_createForm();
		}
		$this->_details[$name]	= ($type) ?: 'text';

		return $this->_detailForm->add($name, $type, $label, $options);
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

	public function getDataType($name)
	{
		if (!array_key_exists($name, $this->_details)) {
			throw new \Exception('Detail "' . $name . '" does not exist!');
		}

		return $this->_details[$name];
	}

	public function _createForm()
	{
		if (!$this->_product) {
			throw new \LogicException('Must add a product before building the form');
		}

		$this->_detailForm	= $this->_services['form']->setName('product-details-edit');
		$this->_detailForm->setDefaultValues($this->_product->details->flatten());
		$this->setFields();
	}
}