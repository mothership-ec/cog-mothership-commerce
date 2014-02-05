<?php

namespace Message\Mothership\Commerce\Product\Type;

use Message\Mothership\Commerce\Product\Product;

class MusicProductType extends AbstractProductType
{
	public function getDisplayName()
	{
		return 'Music release';
	}

	public function getDescription()
	{
		return 'A musical release';
	}

	public function getName()
	{
		return 'music';
	}

	public function getProductDisplayName(Product $product = null)
	{

	}

	public function setFields()
	{
		$this->add('artist', 'datalist', 'Artist', array(
			'choices'	=> $this->_getArtists()
		));
		$this->add('title');
		$this->add('label', 'datalist', 'Label', array(
			'choices'	=> $this->_getLabels()
		))
			->val()->optional();
		$this->add('releaseDate', 'date');

		return $this;

	}

	protected function _getArtists()
	{
		$result	= $this->_services['db.query']->run("
			SELECT DISTINCT
				value
			FROM
				product_detail
			WHERE
				name	= :artist?s
		", array(
			'artist'	=> 'artist',
		));

		return $result->flatten();
	}

	protected function _getLabels()
	{
		$result	= $this->_services['db.query']->run("
			SELECT DISTINCT
				value
			FROM
				product_detail
			WHERE
				name	= :label?s
		", array(
			'label'	=> 'label',
		));

		return $result->flatten();
	}

	protected function _getDefaultValues()
	{
		$defaultValues	= parent::_getDefaultValues();
		$defaultValues['title']	 = (!empty($defaultValues['title'])) ? $defaultValues['title'] : $this->_product->name;

		return $defaultValues;

	}
}