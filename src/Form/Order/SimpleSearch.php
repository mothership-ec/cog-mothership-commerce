<?php

namespace Message\Mothership\Commerce\Form\Order;

use Symfony\Component\Form;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SimpleSearch extends Form\AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(Form\FormBuilderInterface $builder, array $options)
    {
        $builder->add('term', 'search', [
            'label'       => 'Search',
            'attr' => [
                'placeholder' => 'Search orders...',
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'order_simple_search';
    }

}