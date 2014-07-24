<?php

namespace Message\Mothership\Commerce\Product;

class ProductProxy extends Product
{
	public $units = [];

	protected $_loaders;

	public function __construct(
		Locale $locale,
		array $entities = array(),
		array $priceTypes = array(),
		EntityLoaderCollection $loaders
	) {
		parent::construct($locale, $entities, $priceTypes);

		$this->_loaders = $loaders;
	}

	public function getUnits($showOutOfStock = true, $showInvisible = false)
	{
		$this->units = $this->_loaders->get('units')
			->includeOutOfStock($showOutOfStock)
			->includeInvisible($showInvisible)
			->getByProduct($this);

		return $this->units;
	}

	public function getImages($type = 'default', array $options = null)
	{
		$this->_load('images');

		return parent::getFile();
	}

	protected function _load($entity)
	{
		if (!$this->_loaders->exists($entity)) {
			return;
		}

		$this->$entity = $this->_loaders->get($entity)->getByProduct($this);
		$this->_loaders->remove($entity);
	}
}