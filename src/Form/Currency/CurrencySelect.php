<?php

namespace Message\Mothership\Commerce\Form\Currency;

use Symfony\Component\Form;
use Symfony\Component\Validator\Constraints;

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