<?php

namespace Message\Mothership\Commerce\Product\Upload;

use Message\Mothership\Commerce\Product;
use Message\Cog\Event\Dispatcher;

class ProductCreateDispatcher
{
	private $_productCreate;
	private $_detailsEdit;
	private $_dispatcher;

	public function __construct(
		Product\Create $productCreate,
		Product\Type\DetailEdit $detailsEdit,
		Dispatcher $dispatcher
	)
	{
		$this->_productCreate = $productCreate;
		$this->_dispatcher    = $dispatcher;
		$this->_detailsEdit   = $detailsEdit;
	}

	public function create(Product\Product $product, array $formData, array $row)
	{
		$product = $this->_productCreate->create($product);
		$this->_detailsEdit->save($product);

		return $this->_dispatchEvent($product, $formData, $row);
	}

	private function _dispatchEvent(Product\Product $product, array $formData, array $row)
	{
		$event = new ProductCreateEvent($product, $formData, $row);

		return $this->_dispatcher->dispatch(
			Product\Events::PRODUCT_UPLOAD_CREATE,
			$event
		)->getProduct();
	}
}