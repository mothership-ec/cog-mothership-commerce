<?php

namespace Message\Mothership\Commerce\Task\Porting;

class User extends Porting
{

    public function process()
    {
        $uwOld = $this->getFromConnection();
		$uwNew = $this->getToCOnnection();

		$new = new \Message\Cog\DB\Transaction($uwNew);
		$old = new \Message\Cog\DB\Query($uwOld);

		$sql = 'SELECT
					user_id,
					UNIX_TIMESTAMP(sign_up_date) AS created_at,
					NULL AS created_by,
					NULL AS updated_by,
					NULL AS updated_at,
					email_name AS email,
					password,
					1 AS email_confirmed,
					title_name AS title,
					user_forename AS forename,
					user_surname AS surname,
					NULL AS last_login_at,
					NULL AS password_request_at
				FROM
					val_user
				JOIN
					lkp_user_email USING (user_id)
				JOIN
					val_email USING (email_id)
				LEFT JOIN
					val_title ON (val_title.title_id = val_user.user_title_id)
				JOIN
					att_user_password USING (user_id)';

		$result = $old->run($sql);
		$new->add('TRUNCATE product_unit_stock');
		$new->add('TRUNCATE product_unit_stock_snapshot');

		$output= '';
		foreach($result as $row) {

			$new->add('
				INSERT INTO
					user
				(
					user_id,
					created_at,
					created_by,
					updated_by,
					updated_at,
					email,
					password,
					email_confirmed,
					title,
					forename,
					surname,
					last_login_at,
					password_request_at
				)
				VALUES
				(
					:user_id?,
					:created_at?,
					:created_by?,
					:updated_by?,
					:updated_at?,
					:email?,
					:password?,
					:email_confirmed?,
					:title?,
					:forename?,
					:surname?,
					:last_login_at?,
					:password_request_at?
				)', (array) $row);
		}

		if ($new->commit()) {
        	$output.= '<info>Successful</info>';
		}

		return $ouput;
    }
}