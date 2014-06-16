<?php

namespace Message\Mothership\Commerce\Product\Form;

use Message\Cog\Localisation\Translator;

use Symfony\Component\Form;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProductSearch extends Form\AbstractType
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
		return 'product_search';
	}

	public function buildForm(Form\FormBuilderInterface $builder, array $options)
	{
		$builder->add('terms', 'search', [
			'label' => 'ms.commerce.product.search.label',
			'attr'  => array(
				'placeholder' => $this->_trans->trans('ms.commerce.product.search.placeholder') . '&hellip;'
			)
		]);
	}
}