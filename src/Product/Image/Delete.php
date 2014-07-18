<?php

namespace Message\Mothership\Commerce\Product\Image;

use Message\Cog\DB;

class Delete implements DB\TransactionalInterface
{
	protected $_trans;
	protected $_currentUser;

	protected $_transOverridden = false;

	/**
	 * Constructor
	 * 
	 * @param DBTransaction $trans
	 * @param UserInterface $currentUser
	 */
	public function __construct(DB\Transaction $trans, UserInterface $currentUser)
	{
		$this->trans        = $trans;
		$this->_currentUser = $currentUser;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setTransaction (DB\Transaction $trans)
	{
		$this->_trans           = $trans;
		$this->_transOverridden = true;
	}

	/**
	 * Deletes the given image
	 * 
	 * @param  Image  $image
	 */
	public function delete ($imageId)
	{
		$image->authorship->delete();

		$this->_trans->add('
			DELETE FROM
				`product_image`
			WHERE
				`image_id` = :image_id?s
			', 
			[ $imageId, ]
			);

		$this->_trans->add('
			DELETE FROM
				`product_image_option`
			WHERE
				`image_id` = ?s
			',
			[ $imageId, ]
			);
		
		if (!$this->_transOverridden) {
			$this->_trans->commit();
		}
	}
}