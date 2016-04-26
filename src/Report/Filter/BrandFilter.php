<?php

namespace Message\Mothership\User\Report\Filter;

use Message\Cog\DB;
use Message\Mothership\Report\Filter\Choices;
use Message\Mothership\Report\Filter\ModifyQueryInterface;

class BrandFilter extends Choices implements ModifyQueryInterface
{
	const NAME = 'brand';

	public function __construct($label = 'Brands', array $choices = null)
	{
		de('HERE?');

		if (null === $choices) {
			return $this->_builderFactory->getQueryBuilder()
				->select('DISTINCT brand')
				->from('product')
				->getQuery()
				->run()
				->flatten();
		}

		$this->setFormChoices($choices);

		parent::__construct(self::NAME, $label, null, true);
	}


	public function apply(DB\QueryBuilder $queryBuilder)
	{
		// Filter brand
		if($this->_filters->exists('brand')){
			$brand = $this->_filters->get('brand');
			if($brand = $brand->getChoices()) {
				is_array($brand) ?
					$queryBuilder->where('product.Brand IN (?js)', [$brand]) :
					$queryBuilder->where('product.Brand = (?s)', [$brand])
				;
			}
		}
	}
}
