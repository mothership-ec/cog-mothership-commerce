<?php

namespace Message\Mothership\Commerce\Product\Type;

use Message\Mothership\Commerce\Product\Product;
use Message\Cog\DB\Transaction;
use Message\Cog\Event\DispatcherInterface;
use Message\Cog\Field\FieldInterface;
use Message\User\UserInterface;

class DetailEdit
{
	protected $_transaction;
	protected $_dispatcher;
	protected $_user;

	protected $_details	= [];

	public function __construct(Transaction $trans, DispatcherInterface $dispatcher, UserInterface $user)
	{
		$this->_transaction	= $trans;
		$this->_dispatcher	= $dispatcher;
		$this->_currentUser	= $user;
	}

	public function save(Product $product)
	{
		$flattened	= $this->_flatten($product->details);

		foreach ($flattened as $detail) {
			$this->_transaction->add('
			INSERT INTO
				product_detail
				(
					product_id,
					name,
					value,
					value_int,
					locale
				)
			VALUES
				(
					:productID?i,
					:name?s,
					:value?s,
					:value?i,
					:locale?s
				)
			ON DUPLICATE KEY UPDATE
				value		= :value?s
			', array_merge($detail, ['productID' => $product->id]));
		}

		$this->_transaction->commit();
	}

	public function updateDetails($data, Details $details)
	{
		foreach ($data as $name => $value) {
			$details->$name->setValue($value);
		}

		return $details;
	}

	protected function _flatten(Details $details)
	{
		$updates	= [];

		foreach ($details as $field) {
			$updates[]	= [
				'name'		=> $field->getName(),
				'value'		=> $field->getValue(),
				'locale'	=> 'EN', // @todo change this when we add localisation
			];
		}

		return $updates;
	}
}