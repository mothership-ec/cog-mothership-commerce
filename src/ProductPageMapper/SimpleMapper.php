<?php

namespace Message\Mothership\Ecommerce\ProductPageMapper;

use Message\Mothership\CMS\Page;
use Message\Mothership\Commerce\Product;

/**
 * Simple product page mapper that relates products to pages through a field
 * name and optional group name.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class SimpleMapper extends AbstractMapper
{
	/**
	 * @{inheritDoc}
	 */
	public function getPagesForProduct(Product\Product $product, array $options = null)
	{
		$params = array(
			'productID' => $product->id,
			'fieldNames' => $this->_validFieldNames,
		);

		$query = '
			SELECT
				page.page_id
			FROM
				page
			JOIN
				page_content AS product_content ON (
					page.page_id = product_content.page_id
				AND product_content.field_name IN (:fieldNames?js)
		';

		if (false === $this->_validGroupNames) {
			$query .= 'AND product_content.group_name = ""';
		}
		elseif (count($this->_validGroupNames)) {
			$query .= 'AND product_content.group_name IN (:groupNames?js)';
			$params['groupNames'] = $this->_validGroupNames;
		}

		$query .= '
				)
			WHERE
				product_content.value_int  = :productID?i
		';

		if (count($this->_validPageTypes)) {
			$query .= ' AND page.type IN (:pageTypes?js)';
			$params['pageTypes'] = $this->_validPageTypes;
		}

		$query .= ' ORDER BY position_left ASC';

		return $this->_loadPages($query, $params);
	}

	/**
	 * @{inheritDoc}
	 */
	public function getProductsForPage(Page\Page $page)
	{
		$params = array(
			'productID' => $product->id,
			'fieldNames' => $this->_validFieldNames,
		);

		$query = '
			SELECT
				product.product_id
			FROM
				product
			JOIN
				page_content ON (
					page_content.value_int = product.product_id
				AND page_content.field_name IN (:fieldNames?js)
		';

		if (false === $this->_validGroupNames) {
			$query .= 'AND product_content.group_name = ""';
		}
		elseif (count($this->_validGroupNames)) {
			$query .= 'AND product_content.group_name IN (:groupNames?js)';
			$params['groupNames'] = $this->_validGroupNames;
		}

		$query .= '
				)
			LEFT JOIN
				page ON (
					page.page_id = page_content.page_id
				)
			WHERE
				page.page_id = :pageID?i
			ORDER BY
				product.product_id ASC
		';

		return $this->_loadProducts($query, $params);
	}
}