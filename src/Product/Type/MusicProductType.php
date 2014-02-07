<?php

namespace Message\Mothership\Commerce\Product\Type;

use Message\Mothership\Commerce\Product\Product;
use Message\Cog\Form\Handler;
use Message\Cog\DB\Query;

class MusicProductType implements ProductTypeInterface
{
	protected $_query;

	public function __construct(Query $query)
	{
		$this->_query	= $query;
	}

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

	public function getFields(Handler $form, Product $product = null)
	{
		if ($product) {
			$form->setDefaultValues($this->_getDefaultValues($product));
		}

		$form->add('artist', 'datalist', 'Artist', array(
			'choices'	=> $this->_getArtists()
		));
		$form->add('title');
		$form->add('label', 'datalist', 'Label', array(
			'choices'	=> $this->_getLabels()
		))
			->val()->optional();
		$form->add('releaseDate', 'date');

		return $form;

	}

	protected function _getArtists()
	{
		$result	= $this->_query->run("
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
		$result	= $this->_query->run("
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

	protected function _getDefaultValues(Product $product)
	{
		$defaultValues	= $product->details->flatten();
		$defaultValues['title']	 = (!empty($defaultValues['title'])) ? $defaultValues['title'] : $product->name;

		return $defaultValues;

	}
}