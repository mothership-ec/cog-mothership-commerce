<?php

namespace Message\Mothership\Commerce\Product;

use Message\Cog\Localisation\Locale;
use Message\Cog\DB\Entity\EntityLoaderCollection;

class ProductProxy extends Product
{
	protected $_loaders;

	public function __construct(
		Locale $locale,
		array $priceTypes = array(),
		EntityLoaderCollection $loaders
	) {
		parent::__construct($locale, $priceTypes);

		$this->_loaders = $loaders;
	}

	public function getUnits($showOutOfStock = true, $showInvisible = false)
	{
		if ($this->_loaders->exists('units')) {
			$this->_loaders->get('units')
				->includeOutOfStock(true)
				->includeInvisible(true);

			$this->_load('units');
		}

		return parent::getUnits($showOutOfStock, $showInvisible);
	}

	public function getUnit($unitID)
	{
		if ($this->_loaders->exists('units')) {
			return $this->_loaders->get('units')->getByID($unitID);
		}

		return parent::getUnit($unitID);
	}

	public function getImages($type = 'default', array $options = null)
	{
		$this->_load('images');

		return parent::getImages();
	}

	protected function _load($entityName)
	{
		if (!$this->_loaders->exists($entityName)) {
			return;
		}

		$entities = $this->_loaders->get($entityName)->getByProduct($this);
		foreach ($entities as $entity) {
 			$this->{'_' . $entityName}->add($entity);
		}
		$this->_loaders->remove($entityName);
	}
}