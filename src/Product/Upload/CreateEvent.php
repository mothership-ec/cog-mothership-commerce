<?php

namespace Message\Mothership\Commerce\Product\Upload;

use Message\Mothership\Commerce\Product;
use Message\Cog\Event\Event as Event;

class CreateEvent extends Event
{
	private $_product;
	private $_formData;
	private $_row;

	public function __create(Product\Product $product, array $formData, array $row)
	{
		$this->setProduct($product);
		$this->setFormData($formData);
		$this->setRow($row);
	}

	public function setProduct(Product\Product $product)
	{
		$this->_product = $product;
	}

	public function getProduct()
	{
		return $this->_product;
	}

	public function setFormData(array $data)
	{
		$this->_formData = $data;
	}

	public function getFormData()
	{
		return $this->_formData;
	}

	public function setRow(array $row)
	{
		$this->_row = $row;
	}

	public function getRow()
	{
		return $this->_row;
	}
}