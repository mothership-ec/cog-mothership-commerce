<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;

class Create extends Controller
{
	public function index()
	{
		return $this->render('::product:create', array(
			'form'  => $this->createForm($this->get('product.form.create')),
		));
	}

	public function process()
	{
		$form = $this->createForm($this->get('product.form.create'));


		// if ($form->isValid() && $data = $form->getFilteredData()) {
		// 	$product					= $this->get('product');
		// 	$product->name				= $data['name'];
		// 	$product->displayName		= $data['display_name'];
		// 	$product->shortDescription 	= $data['short_description'];
		// 	$product->type				= array_key_exists('type', $data) ?
		// 		$this->get('product.types')->get($data['type']) : $this->get('product.types')->getDefault();
		// 	$product->authorship->create(new DateTimeImmutable, $this->get('user.current'));

		// 	$product = $this->get('product.create')->create($product);

		// 	if ($product->id) {
		// 		return $this->redirectToRoute('ms.commerce.product.edit.attributes', array('productID' => $product->id));
		// 	}
		// }

		return $this->render('Message:Mothership:Commerce::product:create', array(
			'form'  => $form,
		));
	}
}
