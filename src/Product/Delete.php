<?php

namespace Message\Mothership\Commerce\Product;

use Message\Cog\ValueObject\DateTimeImmutable;

use Message\Cog\DB;
use Message\Cog\DB\Result;
use Message\User\UserInterface;

/**
 * Decorator for deleting & restoring products.
 */
class Delete
{
	protected $_query;
	protected $_currentUser;

	/**
	 * Constructor.
	 *
	 * @param DB\Query            $query          The database query instance to use
	 * @param UserInterface       $currentUser    The currently logged in user
	 */
	public function __construct(DB\Query $query, UserInterface $user)
	{
		$this->_query           = $query;
		$this->_currentUser     = $user;
	}

	/**
	 * Delete a page by marking it as deleted in the database.
	 *
	 * @param  Product   $product The product to be deleted
	 *
	 * @return Product   The product that was been deleted, with the "delete"
	 *                    authorship data set
	 */
	public function delete(Product $product)
	{
		$product->authorship->delete(new DateTimeImmutable, $this->_currentUser->id);

		$result = $this->_query->run('
			UPDATE
				product
			SET
				deleted_at = :at?d,
				deleted_by = :by?in
			WHERE
				product_id = :id?i
		', array(
			'at' => $product->authorship->deletedAt(),
			'by' => $product->authorship->deletedBy(),
			'id' => $product->id,
		));


		return $product;
	}

	/**
	 * Restores a currently deleted product to its former self.
	 *
	 * @param  Product $product	The product to be restored
	 *
	 * @return Product 		 	The product, with the "delete"
	 *                    			authorship data cleared
	 */
	public function restore(Product $product)
	{
		$product->authorship->restore();

		$result = $this->_query->run('
			UPDATE
				product
			SET
				deleted_at = NULL,
				deleted_by = NULL
			WHERE
				product_id = ?i
		', $product->id);

		return $product;
	}
}