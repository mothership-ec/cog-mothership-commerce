<?php

namespace Message\Mothership\Commerce\Product\Tax\Resolver;

use Message\Mothership\Commerce\Product\Type\ProductTypeInterface;
use Message\Mothership\Commerce\Address\Address;

/**
 * @author Samuel Trangmar-Keates sam@message.co.uk
 *
 * Given a product type and an address, a tax resolver should find and return the
 * corresponding taxes
 */
interface TaxResolverInterface
{
	/**
	 * Get the tax objects based on a product and address
	 * 
	 * @param  Product $product The product type to check
	 * @param  Address $address 
	 * @return TaxRate          The tax rate found
	 */
	public function getProductTaxRates(ProductTypeInterface $productType, Address $address);
}