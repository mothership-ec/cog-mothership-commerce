<?php

namespace Message\Mothership\Commerce\Form\Extension\Type;

use Message\Cog\Localisation\Translator;

use Symfony\Component\Form;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class VariantType extends Form\AbstractType
{
	public function getName()
	{
		return 'product_variant';
	}

	public function buildForm(Form\FormBuilderInterface $builder, array $options)
	{
		$builder
			->add('key', 'hidden')
			->add('value', 'hidden')
		;

		return $this;
	}
}