<?php

namespace Message\Mothership\Commerce\Task\Porting;

class OrderAddress extends Porting
{

    public function process()
    {
        $uwOld = $this->getFromConnection();
		$uwNew = $this->getToCOnnection();

		$new = new \Message\Cog\DB\Transaction($uwNew);
		$newQuery = new \Message\Cog\DB\Query($uwNew);
		$old = new \Message\Cog\DB\Query($uwOld);

		$sql = 'SELECT
					NULL AS address_id,
					order_id,
					CASE address_type_id
						WHEN 1 THEN \'delivery\'
						WHEN 2 THEN \'billing\'
					END AS type,
					IF (order_id >=5000, SPLIT_STR(address_name,\' \',1), NULL) AS title,
					IF (order_id >=5000, SPLIT_STR(address_name,\' \',2), SPLIT_STR(address_name,\' \',1)) AS forename,
					IF (order_id >=5000, SPLIT_STR(address_name,\' \',3), SPLIT_STR(address_name,\' \',2)) AS surname,
					address_address_1 AS line_1,
					address_address_2 AS line_2,
					NULL AS line_3,
					NULL AS line_4,
					address_postcode AS postcode,
					address_country AS country,
					address_country_id AS country_id,
					address_telephone AS telephone,
					address_town AS town,
					address_state_id AS state_id,
					address_state AS state
				FROM
					order_address';

		$result = $old->run($sql);
		$output= '';

		// Check that the function doesn't exist, if not then create it
		$checkFunction = $newQuery->run('SHOW FUNCTION STATUS WHERE name = \'SPLIT_STR\'');

		if (count($newQuery) == 0) {
		$new->add('CREATE FUNCTION SPLIT_STR(
			  x VARCHAR(255),
			  delim VARCHAR(12),
			  pos INT
			)
			RETURNS VARCHAR(255)
			RETURN REPLACE(SUBSTRING(SUBSTRING_INDEX(x, delim, pos),
			       LENGTH(SUBSTRING_INDEX(x, delim, pos -1)) + 1),
			       delim, "")');
		}

		$new->add('TRUNCATE order_address');

		foreach($result as $row) {
			$new->add('
				INSERT INTO
					order_address
				(
					address_id,
					order_id,
					type,
					title,
					forename,
					surname,
					line_1,
					line_2,
					line_3,
					line_4,
					postcode,
					country,
					country_id,
					telephone,
					town,
					state_id,
					state
				)
				VALUES
				(
					:address_id?,
					:order_id?,
					:type?,
					:title?,
					:forename?,
					:surname?,
					:line_1?,
					:line_2?,
					:line_3?,
					:line_4?,
					:postcode?,
					:country?,
					:country_id?,
					:telephone?,
					:town?,
					:state_id?,
					:state?
				)', (array) $row
			);
		}

		if ($new->commit()) {
        	$output.= '<info>Successful</info>';
		}

		return $ouput;
    }
}