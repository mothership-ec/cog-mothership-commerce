<?php

namespace Message\Mothership\Commerce\Product\Tax;

use Message\Mothership\Commerce\Address\Address;
use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\Tax\Resolver\TaxResolverInterface;
use Message\Mothership\Commerce\Product\ProductEntityLoaderInterface;
use Message\Mothership\Commerce\Product\Loader as ProductLoader;

/**
 * @author Samuel Trangmar-Keates sam@message.co.uk
 *
 * The class for loading in taxes.
 */
class TaxLoader implements ProductEntityLoaderInterface
{
	private $_defaultAddress;
	private $_taxResolver;
	private $_productLoader;

	public function __construct(TaxResolverInterface $taxResolver, Address $defaultAddress)
	{
		$this->_taxResolver    = $taxResolver;
		$this->_defaultAddress = $defaultAddress;
	}

	/**
	 * Sets the default address to use if no address given.
	 * This is mostly useful for "inclusive" tax rates.
	 * 
	 * @param Address $address The address to set.
	 */
	public function setDefaultAddress(Address $address)
	{
		$this->_defaultAddress = $address;
	}

	/**
	 * Get the tax rate for an item to an address. If address is null,
	 * a default address should be used.
	 * 
	 * @param  Product    $product The product to calulate tax for
	 * @param  Address    $address The address delvered to
	 * @return Collection          The tax rates to be applied
	 * @throws AddressNotSetException If no address given or default address set
	 */
	public function getProductTaxRates(Product $product, Address $address = null)
	{
		if ($address === null) {
			$address = $this->_defaultAddress;
		}

		if (!$address) {
			throw new \LogicException('No address given and no default address set. Either provide an address or ensure a default address is set on the loader.');
		}

		return $this->_taxResolver->getProductTaxRates($product, $address);
	}

	/**
	 * Use getProductTaxRates if custom address required
	 * 
	 * {@inheritDoc}
	 */
	public function getByProduct(Product $product)
	{
		$this->getProductTaxRates($product);
	}

	/**
	 * {@inheritDoc}
	 */
	public function setProductLoader(ProductLoader $loader)
	{
		$this->_productLoader = $loader;
	}
}