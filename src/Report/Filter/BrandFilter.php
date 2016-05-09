<?php

namespace Message\Mothership\Commerce\Report\Filter;

use Message\Cog\DB;
use Message\Mothership\Report\Filter\Choices;
use Message\Mothership\Report\Filter\ModifyQueryInterface;

class BrandFilter extends Choices implements ModifyQueryInterface
{
	const NAME = 'brand';

	public function __construct($label = 'Brands', array $choices = [])
	{
		$this->setFormChoices($choices);
		parent::__construct(self::NAME, $label, null, true);
	}


	public function apply(DB\QueryBuilder $queryBuilder)
	{
		$queryString = $queryBuilder->getQueryString();

		if (strpos($queryString, 'JOIN product')
			|| strpos($queryString, 'FROM product')){
			// Filter brand
			if ($brand = $this->getChoices()) {
				is_array($brand) ?
				$queryBuilder->where('product.brand IN (?js)', [$brand]) :
				$queryBuilder->where('product.brand = (?s)', [$brand])
				;
			}
		}
		if (strpos($queryString, 'JOIN order_shipping')
			|| strpos($queryString, 'FROM order_shipping')){
				$queryBuilder->where('1 = 2');
		}
	}
}
