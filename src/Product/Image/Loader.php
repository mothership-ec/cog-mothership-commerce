<?php

namespace Message\Mothership\Commerce\Product\Image;

use Message\Mothership\Commerce\Product\Product;
use Message\Cog\DB\Query;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Mothership\FileManager\File\File;
use Message\Mothership\FileManager\File\Loader as FileLoader;

class Loader
{
	protected $_query;
	protected $_fileLoader;

	public function __construct(Query $query, FileLoader $loader)
	{
		$this->_query      = $query;
		$this->_fileLoader = $loader;
	}

	/**
	 * loads an image by an id
	 * @param  string $id id of image
	 * @return Image
	 */
	public function getByID($id)
	{
		$dataSet = $this->_query->run(
			"SELECT
				image_id
			FROM
				product_image
			WHERE
				image_id = ?s
			", [ $id, ]);

		$all   = $this->_load($dataSet->flatten());
		$image = array_pop($all);

		return $image ?: false;
	}

	/**
	 * loads images from corresponding File
	 * @param  File   $file file to load images by
	 * @return array       array of Image objects with id as key
	 */
	public function getByFile(File $file)
	{
		$dataSet = $this->_query->run(
			"SELECT
				image_id
			FROM
				product_image
			WHERE
				file_id = ?i
			",  $file->id
			);

		$images = $this->_load($dataSet->flatten());

		return $images ?: false;
	}

	/**
	 * Gets images by product
	 * @param  Product $product product's images
	 * @return array           array of products
	 */
	public function getByProduct(Product $product)
	{
		$dataSet = $this->_query->run(
			"SELECT
				image_id
			FROM
				product_image
			WHERE
				product_id = ?i
			", $product->id);

		$images = $this->_load($dataSet->flatten(), $product);

		return $images ?: false;
	}

	protected function _load(array $ids, Product $product = null)
	{
		if (empty($ids)) {
			return [];
		}

		// query for product images and product image options
		$dataSet = $this->_query->run(
			"SELECT
				product_image.product_id   AS productID,
				product_image.image_id     AS id,
				product_image.file_id      AS fileID,
				product_image.type         AS type,
				product_image.created_at   AS createdAt,
				product_image.created_by   AS createdBy,
				product_image.locale       AS locale,
				product_image.product_id   AS productID,
				product_image_option.name  AS optionName,
				product_image_option.value AS optionValue
			FROM
				product_image
			LEFT JOIN
				product_image_option
			ON
				product_image.image_id = product_image_option.image_id
			WHERE
				product_image.image_id IN (?js)
			", [$ids,]
			);

		// bind as image
		$images = $dataSet->bindTo('Message\\Mothership\\Commerce\\Product\\Image\\Image');


		// Images are keyed by id for BC
		$keyedImages = [];

		foreach ($dataSet as $key => $data) {
			if (!array_key_exists($data->id, $keyedImages)) {
				// load extra stuff
				$images[$key]->product = $product;
				$images[$key]->setFileLoader($this->_fileLoader);
				$images[$key]->authorship->create(
					new DateTimeImmutable(date('c', $data->createdAt)),
					$data->createdBy
				);
				$keyedImages[$data->id] = $images[$key];
			}

			// add options
			$keyedImages[$data->id]->options[$data->optionName] = $data->optionValue;
		}

		return $keyedImages;
	}
}