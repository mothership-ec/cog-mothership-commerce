<?php

namespace Message\Mothership\Commerce\Form\Extension\Type;

use Message\Cog\Localisation\Translator;

use Symfony\Component\Form;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UnitType extends Form\AbstractType
{
	/**
	 * @var \Message\Cog\Localisation\Translator
	 */
	protected $_trans;

	public function __construct(Translator $trans)
	{
		$this->_trans = $trans;
	}

	public function getName()
	{
		return 'product_unit';
	}

	public function buildForm(Form\FormBuilderInterface $builder, array $options)
	{
		$builder->add('sku', 'text', [
				'label' => 'ms.commerce.product.units.sku.label',
				'attr'  => [
					'placeholder' => $this->_trans->trans('ms.commerce.product.units.sku.placeholder'),
				],
			])
			->add('stock', 'number', [
				'label' => 'ms.commerce.product.stock.set.label',
				'attr'  => [
					'placeholder' => $this->_trans->trans('ms.commerce.product.stock.set.placeholder'),
				],
			])
			->add('price', 'number', [
				'label' => 'ms.commerce.product.create.price.label',
				'attr'  => [
					'placeholder' => $this->_trans->trans('ms.commerce.product.create.price.placeholder'),
				],
			])
		;

		$builder->add('variants', 'collection', [
				'type'      => 'product_variant',
				'allow_add' => true,
				'prototype_name' => '__variant__',
				'label' => false,
			]);
	
		return $this;
	}
}