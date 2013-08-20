<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;

class Create extends Controller
{
	public function index()
	{
		return $this->render('::product:create', array(
			'form'  => $this->_getForm(),
		));
	}

	public function process()
	{
		$form = $this->_getForm();
		if ($form->isValid() && $data = $form->getFilteredData()) {
			$product = $this->get('product');
			$product->name = $data['name'];
			$product->displayName = $data['display_name'];
			$product->shortDescription = $data['short_description'];
			$product->authorship->create(new DateTimeImmutable, $this->get('user.current'));

			$product = $this->get('product.create')->save($product);

			if ($product->id) {
				return $this->redirectToRoute('ms.commerce.product.edit', array('productID' => $product->id));
			}
		}
	}

	protected function _getForm()
	{
		$form = $this->get('form')
			->setName('product-create')
			->setAction($this->generateUrl('ms.commerce.product.create.action'))
			->setMethod('post');

		$form->add('name', 'text', $this->trans('ms.commerce.product.label.name'))
			->val()->maxLength(255);

		$form->add('display_name', 'text', $this->trans('ms.commerce.product.label.display-name'))
			->val()->maxLength(255);

		$form->add('short_description', 'textarea', $this->trans('ms.commerce.product.label.short-description'));

		return $form;
	}
}
