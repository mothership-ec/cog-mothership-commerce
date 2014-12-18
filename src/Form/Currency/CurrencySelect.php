<?php

namespace Message\Mothership\Commerce\Form\Currency;

use Symfony\Component\Form;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CurrencySelect extends Form\AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(Form\FormBuilderInterface $builder, array $options)
    {
        $builder->add('currency', 'currency_select', [
        	'constraints' => [
				new Constraints\NotBlank,
			],
            'data' => isset($options['data']['currency'])?$options['data']['currency']:null,
            'expanded' => $options['expanded'],
		]);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
           'expanded' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'currency_select_form';
    }

}