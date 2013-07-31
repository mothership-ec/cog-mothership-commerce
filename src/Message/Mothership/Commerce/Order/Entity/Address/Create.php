<?php

namespace Message\Mothership\Commerce\Order\Entity\Address;

use Message\Mothership\Commerce\Order;

use Message\Cog\DB;

/**
 * Order address creator.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Create implements DB\TransactionalInterface
{
	protected $_query;

	public function __construct(DB\Query $query)
	{
		$this->_query = $query;
	}

	public function setTransaction(DB\Transaction $trans)
	{
		$this->_query = $trans;
	}

	public function create(Address $address)
	{
		$this->_query->add('
			INSERT INTO
				order_address
			SET
				order_id   = :orderID?i,
				type       = :type?s,
				name       = :name?sn,
				line_1     = :line1?sn,
				line_2     = :line2?sn,
				line_3     = :line3?sn,
				line_4     = :line4?sn,
				postcode   = :postcode?sn,
				country    = :country?sn,
				country_id = :countryID?sn,
				telephone  = :telephone?sn,
				town       = :town?sn,
				state_id   = :stateID?sn,
				state      = :state?sn
		', array(
			'orderID'   => $address->order->id,
			'type'      => $address->type,
			'name'      => $address->name,
			'line1'     => $address->lines[1],
			'line2'     => $address->lines[2],
			'line3'     => $address->lines[3],
			'line4'     => $address->lines[4],
			'postcode'  => $address->postcode,
			'country'   => $address->country,
			'countryID' => $address->countryID,
			'telephone' => $address->telephone,
			'town'      => $address->town,
			'stateID'   => $address->stateID,
			'state'     => $address->state,
		));

		// use address loader to re-load this item and return it ONLY IF NOT IN ORDER CREATION TRANSACTION
		return $address;
	}
}