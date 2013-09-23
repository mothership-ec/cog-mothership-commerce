<?php

namespace Message\Mothership\Commerce\Controller\Module;

use Message\Cog\Controller\Controller;
use Message\Mothership\CMS\Page\Content;

class ProductSelector extends Controller
{
	protected $_product;

	public function __construct()
	{

	}

	public function index($productID)
	{
		$this->_product = $this->get('product.loader')->getByID($productID);

		return $this->render('Message:Mothership:Commerce::product:product-selector', array(
			'product'   => $this->_product,
			'form' => $this->getForm(),
		));
	}

	public function getForm()
	{
		$form = $this->get('form');
		$form->setName('select_product')
			->setAction($this->generateUrl('ms.commerce.product.add.basket',array('productID' => $this->_product->id)))
			->setMethod('post');

		$choices = array();
		foreach($this->_product->units->all() as $unit) {
			$choices[$unit->id] = ucwords(implode(' / ',$unit->options));
		}

		$form->add('unit_id', 'choice', '', array(
			'choices' => $choices
		));

		return $form;
	}

	public function process($productID)
	{
		$this->_product = $this->get('product.loader')->getByID($productID);

		$form = $this->getForm();

		if ($form->isValid() && $data = $form->getFilteredData()) {
			$unit = $this->_product->getUnit($data['unit_id']);
			$basket = $this->get('basket');
			$location = $this->get('stock.locations')->get('web');
			if ($basket->addItem($unit, $location)) {
				$this->addFlash('success', 'The item has been added to your basket');
			}
		}

		return $this->redirectToReferer();
	}

}