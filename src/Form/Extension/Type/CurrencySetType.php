<?php

namespace Message\Mothership\Commerce\Form\Extension\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @author Iris Schaffer <iris@message.co.uk>
 */
class CurrencySetType extends AbstractType
{
    protected $_currencies;

    public function __construct(array $currencies)
    {
        $this->_currencies = $currencies;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $currencies = $options['currencies'];
        $type = $options['type'];

        // sets options for all currency fields
        // using closure here, because we might want to pass in this closure
        // for other field types
        $childOptions = function(array $options, $currencyID) {
            $childOptions = $options['options'];
            $childOptions['property_path'] = '[' . $currencyID . ']';

            // if child field is money field, add 'currency'-option
            if($options['type'] === 'money') {
                $childOptions['currency'] = $currencyID;
            }

            return $childOptions;
        };

        foreach($currencies as $currencyID) {
            $builder->add($currencyID, $type, $childOptions($options, $currencyID));
        }
    }

    /**
     * Adds the 'currencies'-option which defaults to $_currencies
     * and 'type'-option which defaults to 'money'.
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setRequired([
            'currencies',
            'type'
        ]);
        $resolver->setOptional([
            'options',
        ]);

        $resolver->setDefaults([
            'currencies' => $this->_currencies,
            'type'       => 'money',
            'options'    => array(),
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'currency_set';
    }
}
