<?php

namespace Message\Mothership\Commerce\Form\Extension;

use Symfony\Component\Form\AbstractExtension;

/**
 * Extension for Commerce-specific types
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class CommerceExtension extends AbstractExtension
{

    protected $_currencies;

    public function __construct(array $currencies)
    {
        $this->_currencies = $currencies;
    }

    protected function loadTypes()
    {
        return array(
            new Type\CurrencySetType($this->_currencies),
        );
    }
}
