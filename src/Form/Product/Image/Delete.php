<?php

namespace Message\Mothership\Commerce\Form\Product\Image;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form;
use Symfony\Component\Validator\Constraints;

class Delete extends Form\AbstractType
{
	public function getName()
	{
		return 'ms_product_image_delete';
	}

	public function buildForm(Form\FormBuilderInterface $builder, array $options)
	{}
}