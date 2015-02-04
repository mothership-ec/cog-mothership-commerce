<?php

namespace Message\Mothership\Commerce\Product\Type;

use Message\Mothership\Commerce\Product\Product;

use Message\Cog\Field\Factory;
use Message\Cog\DB\QueryBuilderFactory;

/**
 * Class BookProductType
 * @package Message\Mothership\Commerce\Product\Type
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class BookProductType implements ProductTypeInterface
{
	/**
	 * @var \Message\Cog\DB\QueryBuilderFactory
	 */
	private $_qbFactory;

	public function __construct(QueryBuilderFactory $qbFactory)
	{
		$this->_qbFactory = $qbFactory;
	}

	public function getName()
	{
		return 'book';
	}

	public function getDisplayName()
	{
		return 'Book';
	}

	public function getDescription()
	{
		return 'A book or leaflet';
	}

	public function getProductDisplayName(Product $product)
	{
		$name = $product->displayName ?: $product->name;

		return $product->getDetails()->author ? $product->getDetails()->author .' - '  . $name : $name;
	}

	public function setFields(Factory $factory, Product $product = null)
	{
		$factory->add($factory->getField('datalist', 'author', 'Author')->setFieldOptions([
			'choices'	  => $this->_getAuthors(),
		]));
		$factory->add($factory->getField('text', 'title', 'Title'));
		$factory->add($factory->getField('date', 'releaseDate', 'Release date'));
	}

	private function _getAuthors()
	{
		return $this->_qbFactory->getQueryBuilder()
			->select('value', true)
			->from('product_detail')
			->where('name = ?s', ['author'])
			->getQuery()
			->run()
			->flatten()
		;
	}
}