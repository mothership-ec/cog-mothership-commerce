<?php

namespace Message\Mothership\Commerce\Product\Upload;

use Message\Mothership\Commerce\Product;
use Message\Cog\Event\Dispatcher;

class CreateDispatcher
{
	private $_productCreate;
	private $_dispatcher;

	public function __construct(Product\Create $productCreate, Dispatcher $dispatcher)
	{
		$this->_productCreate = $productCreate;
		$this->_dispatcher    = $dispatcher;
	}

	public function create(Product\Product $product, array $formData, array $row)
	{
		$product = $this->_productCreate->create($product);

		return $this->_dispatchEvent($product, $formData, $row);
	}

	private function _dispatchEvent(Product\Product $product, array $formData, array $row)
	{
		return $this->_dispatcher->dispatch(
			Product\Events::PRODUCT_UPLOAD_CREATE,
			new CreateEvent($product, $formData, $row)
		)->getProduct();
	}
}