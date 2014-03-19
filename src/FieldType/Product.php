<?php

namespace Message\Mothership\Commerce\FieldType;

use Message\Cog\Field\Field;

use Message\Mothership\FileManager\File\Type as FileType;

use Message\Cog\Filesystem;
use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;
use Symfony\Component\Form\FormBuilder;

/**
 * A field for a product from the products database.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Product extends Field implements ContainerAwareInterface
{
	protected $_services;
	protected $_product;

	/**
	 * {@inheritdoc}
	 */
	public function setContainer(ContainerInterface $container)
	{
		$this->_services = $container;
	}

	public function getFieldType()
	{
		return 'product';
	}

	public function getFormField(FormBuilder $form)
	{
		$form->add($this->getName(), 'choice', $this->_getOptions());
	}

	public function getProduct()
	{
		if (null === $this->_product) {
			$this->_product = $this->_services['product.loader']->getByID((int) $this->_value);
		}

		return $this->_product;
	}

	protected function _getOptions()
	{
		$defaults = [
			'choices'       => $this->_getChoices(),
			'empty_value'   => 'Please select a product...',
		];

		return array_merge($defaults, $this->getFieldOptions());
	}

	protected function _getChoices()
	{
		$choices = array();

		foreach ($this->_services['product.loader']->getAll() as $product) {
			$choices[$product->id] = $product->displayName ?: $product->name;
		}

		return $choices;
	}
}