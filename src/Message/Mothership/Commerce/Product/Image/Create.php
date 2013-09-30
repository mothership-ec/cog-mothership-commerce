<?php

namespace Message\Mothership\Commerce\Product\Image;

use Message\Mothership\Commerce\Product\Product;

use Message\Mothership\FileManager\File\File;

use Message\User\UserInterface;

use Message\Cog\Security\Hash\HashInterface;
use Message\Cog\DB;
use Message\Cog\ValueObject\DateTimeImmutable;

class Create implements DB\TransactionalInterface
{
	protected $_query;
	protected $_transOverridden = false;

	protected $_currentUser;

	public function __construct(DB\Transaction $trans, UserInterface $currentUser)
	{
		$this->_query       = $trans;
		$this->_currentUser = $currentUser;
	}

	public function setTransaction(DB\Transaction $trans)
	{
		$this->_query           = $trans;
		$this->_transOverridden = true;
	}

	public function create(Image $image)
	{
		// Set create authorship data if not already set
		if (!$image->authorship->createdAt()) {
			$image->authorship->create(
				new DateTimeImmutable,
				$this->_currentUser->id
			);
		}

		$this->_validate($image);

		// Set the hash
		$image->id = $this->_generateId($image);

		// Create the image
		$this->_query->add('
			REPLACE INTO
				product_image
			SET
				image_id   = :imageID?s,
				created_at = :createdAt?d,
				created_by = :createdBy?in,
				product_id = :productID?i,
				file_id    = :fileID?i,
				type       = :type?s,
				locale     = :locale?s
		', array(
			'imageID'   => $image->id,
			'createdAt' => $image->authorship->createdAt(),
			'createdBy' => $image->authorship->createdBy(),
			'productID' => $image->product->id,
			'fileID'    => $image->file->id,
			'type'      => $image->type,
			'locale'    => $image->locale->getId(),
		));

		// Add the options criteria
		foreach ($image->options as $name => $value) {
			$this->_query->add('
				REPLACE INTO
					product_image_option
				SET
					image_id = :imageID?s,
					name     = :name?s,
					value    = :value?s
			', array(
				'imageID' => $image->id,
				'name'    => $name,
				'value'   => $value,
			));
		}

		// If the transaction was not overwritten, commit the queries
		if (!$this->_transOverridden) {
			$this->_query->commit();
		}

		return $image;
	}

	/**
	 * Validate that a product image can be created in the database.
	 *
	 * @todo Check the options criteria are all options actually set on the
	 *       product itself
	 *
	 * @param  Image  $image The image to check
	 *
	 * @throws \InvalidArgumentException If the product property is not an
	 *                                   instance of Product
	 * @throws \InvalidArgumentException If the file property is not an
	 *                                   instance of File
	 */
	protected function _validate(Image $image)
	{
		if (!($image->product instanceof Product)) {
			throw new \InvalidArgumentException('Cannot create product image: Image must have a valid product set.');
		}

		if (!($image->file instanceof File)) {
			throw new \InvalidArgumentException('Cannot create product image: Image must have a valid file set.');
		}
	}

	protected function _generateId(Image $image)
	{
		$data = array(
			$image->product->id,
			$image->locale->getId(),
			$image->type,
		);

		// Sort options by key for hash consistency
		ksort($image->options);

		$data[] = $image->options;

		return md5(serialize($data));
	}
}