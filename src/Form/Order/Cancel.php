<?php

namespace Message\Mothership\Commerce\Form\Order;

use Symfony\Component\Form;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Message\Mothership\Commerce\Product\Stock\Location\Location;
use Message\Cog\Security\Hash\HashInterface;
use Message\User\User;

class Cancel extends Form\AbstractType
{
	const STOCK_LOCATION_OPTION = 'stock_location';
	const STOCK_LABEL_OPTION    = 'STOCK_LABEL_OPTION';

	protected $_stockLocation;
	protected $_password;
	protected $_passwordHash;

	public function __construct(
		Location $stockLocation,
		$password,
		HashInterface $passwordHash
	) {
		$this->_stockLocation = $stockLocation;
		$this->_password      = $password;
		$this->_passwordHash  = $passwordHash;
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(Form\FormBuilderInterface $builder, array $options)
	{
		$builder->add('stock', 'checkbox', [
			'label' => sprintf(
				'Return %s to stock location `%s`?',
				$options[self::STOCK_LABEL_OPTION],
				$options[self::STOCK_LOCATION_OPTION]->displayName
			),
		]);
		$builder->add('refund', 'checkbox', [
			'label' => 'Issue a refund?',
		]);
		$builder->add('notifyCustomer', 'checkbox', [
			'label' => 'Notify the customer by email?',
		]);
		$builder->add('password', 'password', [
			'label' => 'Please confirm your account password to continue',
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
		$resolver->setRequired([self::STOCK_LOCATION_OPTION, self::STOCK_LABEL_OPTION]);
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

}