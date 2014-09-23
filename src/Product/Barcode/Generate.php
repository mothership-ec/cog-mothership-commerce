<?php

namespace Message\Mothership\Commerce\Product\Barcode;

use Message\Cog\DB\Query;

/**
 * Class Generate
 * @package Message\Mothership\Commerce\Product\Barcode
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class Generate
{
	const BARCODE_LOCATION = 'barcodes';
	const PUBLIC_PATH      = 'cog://public';

	/**
	 * @var \Message\Cog\DB\Query
	 */
	protected $_query;

	/**
	 * @var ImageResource
	 */
	protected $_imageResource;

	/**
	 * @var int
	 */
	protected $_height;

	/**
	 * @var int
	 */
	protected $_width;

	/**
	 * @var string
	 */
	protected $_fileExt;

	/**
	 * @var string
	 */
	protected $_barcodeType;

	/**
	 * @var array
	 */
	protected $_supportedImageTypes = [
		'png',
		'jpg',
		'jpeg',
		'gif',
	];

	/**
	 * @param Query $query                     Database query object for selecting barcode data
	 * @param ImageResource $imageResource     ImageResource object for handling the barcode files
	 * @param $height int                      Height of barcode to generate (pixels)
	 * @param $width int                       Width of barcode to generate (1 is a standard)
	 * @param $fileExt string                  File extension for marcode images
	 * @param $type string                     Type of barcode, i.e. 'code39'
	 */
	public function __construct(Query $query, ImageResource $imageResource, $height, $width, $fileExt, $type)
	{
		$this->_query         = $query;
		$this->_imageResource = $imageResource;

		$this->setHeight($height);
		$this->setWidth($width);
		$this->setFileExt($fileExt);
		$this->setBarcodeType($type);
	}

	public function getOneOfEach()
	{
		return $this->_getBarcodes();
	}

	/**
	 * @param $quantities array    Array of unit IDs and quanities. The key is the unit ID and the value is the quantity
	 * @param $offset int          Number of empty barcodes to append to list
	 * @throws \LogicException     Throws exception if a barcode is loaded for a unit that isn't in the $quantities array
	 *
	 * @return array               Returns array of barcodes including duplicates and offset
	 */
	public function getUnitBarcodes(array $quantities, $offset = 0)
	{
		$offset   = (int) $offset;
		$unitIDs  = array_keys($quantities);
		$toPrint  = [];
		$barcodes = $this->_getBarcodes($unitIDs);

		if (count($barcodes) === 0) {
			return $toPrint;
		}

		for ($i = 0; $i < $offset; $i++) {
			$toPrint[] = null;
		}

		foreach ($barcodes as $barcode) {
			if (array_key_exists($barcode->unitID, $quantities)) {
				$quantity = (int) $quantities[$barcode->unitID];
				for ($i = 0; $i < $quantity; $i++) {
					$toPrint[] = $barcode;
				}
			}
			else {
				throw new \LogicException($barcode->unitID . ' not set in quantity list');
			}
		}

		return $toPrint;
	}

	/**
	 * @return int
	 */
	public function getHeight()
	{
		return $this->_height;
	}

	/**
	 * @param $height int | float | string
	 * @throws \InvalidArgumentException
	 *
	 * @return Generate
	 */
	public function setHeight($height)
	{
		if (!is_numeric($height)) {
			throw new \InvalidArgumentException('$height must be a numeric value');
		}

		$this->_height = (int) $height;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getWidth()
	{
		return $this->_width;
	}

	/**
	 * @param $width int | float | string
	 * @throws \InvalidArgumentException
	 *
	 * @return Generate
	 */
	public function setWidth($width)
	{
		if (!is_numeric($width)) {
			throw new \InvalidArgumentException('$width must be a numeric value');
		}

		$this->_width = (int) $width;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getFileExt()
	{
		return $this->_fileExt;
	}

	/**
	 * @param $ext string
	 * @throws \InvalidArgumentException
	 *
	 * @return Generate
	 */
	public function setFileExt($ext)
	{
		if (!in_array($ext, $this->_supportedImageTypes)) {
			$error = (is_string($ext)) ?
				$ext . ' is not a supported file extension' :
				'$ext must be a string, ' . gettype($ext) . ' given';

			throw new \InvalidArgumentException($error);
		}

		$this->_fileExt = $ext;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getBarcodeType()
	{
		return $this->_barcodeType;
	}

	/**
	 * @param $type
	 * @throws \InvalidArgumentException
	 *
	 * @return Generate
	 */
	public function setBarcodeType($type)
	{
		if (!is_string($type)) {
			throw new \InvalidArgumentException('$type must be a string, ' . gettype($type) . ' given');
		}

		$this->_barcodeType = $type;

		return $this;
	}

	/**
	 * This loads only the information we need, and assigns it to a Barcode object. As it stands the system does not
	 * load the products efficiently enough and will break if the load is too high, so this binds to a lightweight
	 * Barcode object to mitigate this problem, see https://github.com/messagedigital/cog-mothership-commerce/issues/297
	 *
	 * @param $unitIDs array                  Not currently used but will be useful when dealing with individual units
	 *
	 * @return \Message\Cog\DB\Result
	 */
	protected function _getBarcodes($unitIDs = [])
	{
		$barcodes = $this->_query->run("
			SELECT DISTINCT
				u.unit_id AS unitID,
				p.brand,
				p.name,
				u.barcode,
				IFNULL(up.price, pp.price) AS price,
				IFNULL(up.currency_id, pp.currency_id) AS currency,
				GROUP_CONCAT(DISTINCT o.option_value SEPARATOR ', ') AS text
			FROM
				product_unit AS u
			LEFT JOIN
				product AS p
			USING
				(product_id)
			LEFT JOIN
				product_unit_option AS o
			USING
				(unit_id)
			LEFT JOIN
				product_unit_price AS up
			ON
				(u.unit_id = up.unit_id AND up.type = :retail?s AND up.currency_id = :currencyID?s)
			LEFT JOIN
				product_info AS pi
			USING
				(product_id)
			LEFT JOIN
				product_price AS pp
			ON
				(u.product_id = pp.product_id AND pp.type = :retail?s AND pp.currency_id = :currencyID?s)
			WHERE
				barcode IS NOT NULL
			AND
				barcode != ''
			" . ($unitIDs ? "AND u.unit_id IN (:unitIDs?ij)" : "") . "
			GROUP BY
				u.unit_id
			ORDER BY
				p.category ASC,
				COALESCE(
					CONCAT(pi.sort_name, p.name),
					p.name
				)
		", [
			'retail'     => 'retail',
			'currencyID' => 'GBP',
			'unitIDs'    => $unitIDs,
		])->bindTo('\\Message\\Mothership\\Commerce\\Product\\Barcode\\Barcode');

		foreach ($barcodes as $barcode) {
			$code          = $barcode->getBarcode();
			$barcode->text = trim($barcode->text, ' ,');
			$barcode->file = $this->_getBarcodeImage($code);
			$barcode->url  = $barcode->file->getPublicUrl();
		}

		return $barcodes;
	}

	/**
	 * Method to return the url of the barcode image file. If no image exists, it creates the image and saves it to
	 * the file system
	 *
	 * @param $barcode
	 *
	 * @return string       Returns location of the barcode image file
	 */
	protected function _getBarcodeImage($barcode)
	{
		if (!$this->_imageResource->exists($this->_getFilename($barcode))) {
			$image = $this->_imageResource->getResource(
				$barcode,
				$this->getBarcodeType(),
				$this->getFileExt(),
				$this->getHeight(),
				$this->getWidth()
			);

			$this->_imageResource->save($image, $this->_getFilename($barcode), $this->getFileExt());
		}

		return $this->_imageResource->getFile($this->_getFilename($barcode));
	}

	/**
	 * Returns a filename generated using a hash of the barcode and attributes such as the size and file type
	 *
	 * @param $barcode
	 *
	 * @return string
	 */
	protected function _getFilename($barcode)
	{
		$string = $barcode . $this->getFileExt() . $this->getHeight() .	$this->getWidth() . $this->getBarcodeType();

		return md5($string) . '.' . $this->getFileExt();
	}
}