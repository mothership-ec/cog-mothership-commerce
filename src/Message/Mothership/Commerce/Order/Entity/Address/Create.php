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
	protected $_loader;

	public function __construct(DB\Query $query, Loader $loader)
	{
		$this->_query  = $query;
		$this->_loader = $loader;
	}

	public function setTransaction(DB\Transaction $trans)
	{
		$this->_query = $trans;
	}

	public function create(Address $address)
	{
		$result = $this->_query->run('
			INSERT INTO
				order_address
			SET
				order_id   = :orderID?i,
				type       = :type?s,
				title      = :title?sn,
				forename   = :forename?sn,
				surname    = :surname?sn,
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
			'title'     => $address->title,
			'forename'  => $address->forename,
			'surname'   => $address->surname,
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

		if ($this->_query instanceof DB\Transaction) {
			return $address;
		}

		return $this->_loader->getByID($result->id(), $address->order);
	}
}