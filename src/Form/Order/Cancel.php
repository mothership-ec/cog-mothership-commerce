<?php

namespace Message\Mothership\Commerce\Form\Order;

use Symfony\Component\Form;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Message\Mothership\Commerce\Product\Stock\Location\Location;
use Message\Cog\Security\Hash\HashInterface;
use Message\User\User;
use Message\Cog\Localisation\Translator;

class Cancel extends Form\AbstractType
{
	const STOCK_LOCATION_OPTION = 'stock_location';
	const STOCK_LABEL_OPTION    = 'stock_label';
	const REFUNDABLE_OPTION     = 'refundable';

	protected $_stockLocation;
	protected $_password;
	protected $_passwordHash;
	private $_translator;

	public function __construct(
		Location $stockLocation,
		$password,
		HashInterface $passwordHash,
		Translator $trans
	) {
		$this->_stockLocation = $stockLocation;
		$this->_password      = $password;
		$this->_passwordHash  = $passwordHash;
		$this->_translator    = $trans;
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(Form\FormBuilderInterface $builder, array $options)
	{
		$trans = $this->getTranslator();

		$builder->add('stock', 'checkbox', [
			'label' =>
				$trans->trans('ms.commerce.order.order.cancel-form.return-stock', [
					'%items%' => $options[self::STOCK_LABEL_OPTION],
					'%location%' => $options[self::STOCK_LOCATION_OPTION]->displayName
				]),
			]);

		if ($options[self::REFUNDABLE_OPTION]) {
			$builder->add('refund', 'checkbox', [
				'label' => $trans->trans('ms.commerce.order.order.cancel-form.issue-refund'),
			]);	
		}

		$builder->add('notifyCustomer', 'checkbox', [
			'label' => $trans->trans('ms.commerce.order.order.cancel-form.notify-customer'),
		]);
		$builder->add('password', 'password', [
			'label' => $trans->trans('ms.commerce.order.order.cancel-form.password-confirm'),
			'constraints' => [
				new Constraints\NotBlank,
			]
		]);

		$builder->addEventListener(Form\FormEvents::POST_SUBMIT, [$this, 'onPostSubmit'], 900);
	}

	public function onPostSubmit(Form\FormEvent $event)
	{
		$this->_validatePassword($event);
	}

	protected function _validatePassword(Form\FormEvent $event)
	{
		$data = $event->getData();
		$form = $event->getForm();

		if (!$this->_passwordHash->check($data['password'], $this->_password)) {
			$form->get('password')->addError(new Form\FormError(
				'The password is not correct.'
			));
		}
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setRequired([
			self::STOCK_LOCATION_OPTION,
			self::STOCK_LABEL_OPTION,
			self::REFUNDABLE_OPTION,
		]);
		$resolver->setAllowedTypes([
			self::STOCK_LOCATION_OPTION => 'Message\\Mothership\\Commerce\\Product\\Stock\\Location\\Location',
		]);
		$resolver->setDefaults([
		   self::STOCK_LOCATION_OPTION => $this->_stockLocation,
		   self::STOCK_LABEL_OPTION => 'item(s)',
		]);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'order_cancel';
	}


    /**
     * Gets the value of _translator.
     *
     * @return Translator
     */
    public function getTranslator()
    {
        return $this->_translator;
    }
    
    /**
     * Sets the value of _translator.
     *
     * @param mixed $_translator the  translator 
     *
     * @return self
     */
    public function setTranslator(Translator$translator)
    {
        $this->_translator = $translator;

        return $this;
    }
}