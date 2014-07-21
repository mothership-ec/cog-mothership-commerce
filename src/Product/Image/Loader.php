<?php

namespace Message\Mothership\Commerce\Product\Image;

use Message\Cog\DB\Query;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Mothership\FileManager\File\File;
use Message\Mothership\FileManager\File\Loader as FileLoader;

class Loader
{
	private $_query;
	private $_loaded;
	private $_fileloader;

	public function __construct(Query $query, FileLoader $loader)
	{
		$this->_query      = $query;
		$this->_fileloader = $loader;
	}

	public function loadByID($id)
	{
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
				product_image.image_id = ?s
			", [ $id, ]);

		return array_pop($this->_load($dataSet));
	}

	public function loadByFile(File $file)
	{
		$dataSet = $this->_query->run(
			"SELECT
				product_image.product_id      AS productID,
				product_image.image_id        AS id,
				product_image.file_id         AS fileID,
				product_image.type            AS type,
				product_image.created_at      AS createdAt,
				product_image.created_by      AS createdBy,
				product_image.locale          AS locale,
				product_image.product_id      AS productID,
				product_image_option.name     AS optionName,
				product_image_option.value    AS optionValue
			FROM
				product_image 
			LEFT JOIN 
				product_image_option 
			ON 
				product_image.image_id = product_image_option.image_id
			WHERE
				product_image.file_id = ?i
			ORDER BY
				product_image.image_id
			", [ $file->id, ]);

		$images = $this->_load($dataSet);

		return $images;
	}

	protected function _load($dataSet)
	{
		$images = [];

		foreach ($dataSet as $data) {
			if (!array_key_exists($data->id, $images)){
				$image                = new Image;
				$image->id            = $data->id;
				$image->type          = $data->type;
				$image->product       = $data->productID;
				$image->locale        = $data->locale;
				$image->fileID        = $data->fileID;
				$image->setFileLoader($this->_fileloader);

				$image->authorship->create(
					new DateTimeImmutable(date('c', $data->createdAt)),
					$data->createdBy
				);
				$imageIDs[$data->id] = $image;
			}

			$images[$data->id]->options[$data->optionName] = $data->optionValue;
		}

		return $images;
	}
}