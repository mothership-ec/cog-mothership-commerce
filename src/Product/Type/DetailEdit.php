<?php

namespace Message\Mothership\Commerce\Product\Type;

use Message\Mothership\Commerce\Product\Product;
use Message\Cog\DB\Transaction;
use Message\Cog\DB\TransactionalInterface;
use Message\Cog\Event\DispatcherInterface;
use Message\Cog\Field\FieldInterface;
use Message\User\UserInterface;

class DetailEdit implements TransactionalInterface
{
	protected $_transaction;
	protected $_dispatcher;
	protected $_user;

	protected $_details	= [];
	protected $_transOverridden = false;

	public function __construct(Transaction $trans, DispatcherInterface $dispatcher, UserInterface $user)
	{
		$this->_transaction  = $trans;
		$this->_dispatcher   = $dispatcher;
		$this->_currentUser  = $user;
	}

	public function save(Product $product)
	{
		$flattened	= $this->_flatten($product->getDetails());

		$this->_transaction->add("
			DELETE FROM
				product_detail
			WHERE
				product_id = :productID?i
		", [
			'productID' => $product->id,
		]);

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
				', array_merge($detail, ['productID' => $product->id]));
		}

		if (!$this->_transOverridden) {
			$this->_transaction->commit();
		}

	}

	public function setTransaction(Transaction $trans)
	{
		$this->_transOverridden = true;

		$this->_transaction = $trans;
	}

	public function updateDetails($data, DetailCollection $details)
	{
		foreach ($data as $name => $value) {
			$details->$name->setValue($value);
		}

		return $details;
	}

	protected function _flatten(DetailCollection $details)
	{
		$updates	= [];

		foreach ($details as $field) {
			if ($field->getValue()) {
				$updates[]	= [
					'name'		=> $field->getName(),
					'value'		=> $field->getValue(),
					'locale'	=> 'EN', // @todo change this when we add localisation
				];
			}
		}

		return $updates;
	}
}