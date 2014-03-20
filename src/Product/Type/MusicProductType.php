<?php

namespace Message\Mothership\Commerce\Product\Type;

use Message\Mothership\Commerce\Product\Product;
use Message\Cog\DB\Query;
use Message\Cog\Field\Factory;
use Symfony\Component\Validator\Constraints;

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
		return $product->details->artist . ' - ' . $product->details->title;
	}

	public function setFields(Factory $factory, Product $product = null)
	{
		$factory->add($factory->getField('datalist', 'artist', 'Artist')->setFieldOptions([
			'choices'	  => $this->_getArtists(),
			'constraints' => [
				new Constraints\NotBlank,
			],
		]));
		$factory->add($factory->getField('text', 'title', 'Title'));
		$factory->add($factory->getField('datalist', 'label', 'Label')->setFieldOptions([
			'choices'	=> $this->_getLabels(),
			'constraints' => [
				new Constraints\NotBlank,
			],
		]));
		$factory->add($factory->getField('date', 'releaseDate', 'Release date')->setFieldOptions([
			'constraints' => [
				new Constraints\NotBlank,
			],
		]));
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