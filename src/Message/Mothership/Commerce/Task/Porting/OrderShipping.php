<?php

namespace Message\Mothership\Commerce\Task\Porting;

use Message\Cog\Console\Task\Task as BaseTask;

class OrderShipping extends BaseTask
{

    public function process()
    {
        $uwOld = new \Message\Cog\DB\Adapter\MySQLi\Connection(array(
				'host'		=> '127.0.0.1',
				'user'		=> 'root',
				'password' 	=> 'chelsea',
				'db'		=> 'uniform_wares',
				'charset'	=> 'utf-8',
		));


		$uwNew = new \Message\Cog\DB\Adapter\MySQLi\Connection(array(
				'host'		=> '127.0.0.1',
				'user'		=> 'root',
				'password' 	=> 'chelsea',
				'db'		=> 'mothership_cms',
				'charset'	=> 'utf-8',
		));

		$new = new \Message\Cog\DB\Transaction($uwNew);
		$old = new \Message\Cog\DB\Query($uwOld);

		$sql = 'SELECT
					order_shipping.order_id,
					IFNULL(shipping_amount,0) AS list_price,
					IFNULL(shipping_amount,0) - IFNULL(shipping_tax,0) AS net,
					0 AS discount,
					IFNULL(shipping_tax,0) AS tax,
					MAX(item_tax_rate) AS tax_rate,
					IFNULL(shipping_amount,0) AS gross,
					CASE shipping_id
						WHEN 1 THEN \'uk_fedex\'
						WHEN 2 THEN \'eu_fedex\'
						WHEN 3 THEN \'us_fedex\'
						WHEN 4 THEN \'row_fedex\'
						WHEN 5 THEN \'free_shipping\'
						WHEN 14 THEN \'uk_gift_voucher\'
						WHEN 15 THEN \'eu_gift_voucher\'
						WHEN 16 THEN \'us_gift_voucher\'
						WHEN 17 THEN \'row_gift_couher\'
						ELSE \'uk_fedex\'
					END AS name
				FROM
					order_shipping
				JOIN
					(
					SELECT
						item_tax_rate,
						order_id
					FROM
						order_item
					ORDER BY item_tax_rate DESC
				) AS tax ON (tax.order_id = order_shipping.order_id)
				GROUP BY order_id
				ORDER BY order_id DESC';

		$result = $old->run($sql);
		$output= '';
		foreach($result as $row) {
			$new->add('
				INSERT INTO
					order_shipping
				(
					order_id,
					list_price,
					net,
					discount,
					tax,
					tax_rate,
					gross,
					name
				)
				VALUES
				(
					:order_id?,
					:list_price?,
					:net?,
					:discount?,
					:tax?,
					:tax_rate?,
					:gross?,
					:name?
				)', (array) $row
			);
		}

		if ($new->commit()) {
        	$output.= '<info>Successful</info>';
		}

		return $ouput;
    }
}