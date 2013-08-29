<?php

namespace Message\Mothership\Commerce\Task\Porting;

class OrderRepair extends Porting
{

    public function process()
    {
        $uwOld = $this->getFromConnection();
		$uwNew = $this->getToCOnnection();

		$new = new \Message\Cog\DB\Transaction($uwNew);
		$old = new \Message\Cog\DB\Query($uwOld);

		$sql = 'SELECT
					repair_id,
					order_id,
					order_item_id,
					catalogue_id,
					product_name,
					repair_notes,
					repair_purchase_date,
					repair_retailer,
					repair_faulty
				FROM
					order_repair';

		$result = $old->run($sql);
		$output= '';

		$new->add('TRUNCATE order_repair');

		foreach($result as $row) {
			$new->add('
				INSERT INTO
					order_repair
				(
					repair_id,
					order_id,
					order_item_id,
					catalogue_id,
					product_name,
					repair_notes,
					repair_purchase_date,
					repair_retailer,
					repair_faulty
				)
				VALUES
				(
					:repair_id?,
					:order_id?,
					:order_item_id?,
					:catalogue_id?,
					:product_name?,
					:repair_notes?,
					:repair_purchase_date?,
					:repair_retailer?,
					:repair_faulty?
				)', (array) $row
			);
		}

		if ($new->commit()) {
        	$output.= '<info>Successfulo</info>';
		}

		return $ouput;
    }
}