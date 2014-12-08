<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1415804834_OrderItemTaxRates extends Migration
{
	public function up()
	{
		$this->run(
			"CREATE TABLE IF NOT EXISTS `order_shipping_tax` (
			  `order_id` int(11) unsigned NOT NULL,
			  `tax_type` varchar(30) NOT NULL,
			  `tax_rate` decimal(10,3) unsigned NOT NULL,
			  `tax_amount` decimal(10,2) unsigned NOT NULL,
			  PRIMARY KEY (`order_id`,`tax_type`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run(
			"INSERT INTO order_item_tax (item_id, tax_type, tax_rate, tax_amount)
			SELECT 
				item_id, 
				'VAT',
				tax_rate,
				tax
			FROM order_item WHERE tax > 0;
		");
	}

	public function down()
	{
		$this->run("DROP TABLE IF EXISTS `order_shipping_tax`; ");
	}
}