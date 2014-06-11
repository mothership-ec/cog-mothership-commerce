<?php

namespace Message\Mothership\Commerce\Field;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class ProductUnitInStockOnlyChoiceType extends AbstractType
{
	protected $_container;

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults([
			'oos'          => [],
			'units'        => [],
			'oos_label'    => '(Out of stock)',
			'show_pricing' => false,
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildView(FormView $view, FormInterface $form, array $options)
	{
		$view->vars = array_replace($view->vars, [
			'oos'          => $options['oos'],
			'oos_label'    => $options['oos_label'],
			'show_pricing' => $options['show_pricing'],
			'units'        => $options['units'],
		]);
	}

	public function getParent()
	{
		return 'choice';
	}

	public function getName()
	{
		return 'ms_product_unit_in_stock';
	}
}

