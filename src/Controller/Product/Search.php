<?php

namespace Message\Mothership\Commerce\Controller\Product;

use \Message\Cog\Controller\Controller;

class Search extends Controller
{
	public function index()
	{
		return $this->render('Message:Mothership:Commerce::product:search', array(
			'form' => $this->getForm(),
		));
	}

	public function process()
	{
		$form = $this->getForm();
		$form->handleRequest();

		if (!$form->isValid() || !$data = $form->getData()) {
			// Add error
			return $this->redirectToReferer();
		}

		$products = $this->get('product.loader')->getBySearchTerms($data['terms'], 50);

		return $this->render('Message:Mothership:Commerce::product:search-results', array(
			'terms'    => $data['terms'],
			'products' => $products,
		));
	}

	public function getForm()
	{
		return $this->createForm($this->get('product.form.search'),
			$this->_getTermsFromRequest(),
			[
				'action' => $this->generateUrl('ms.commerce.product.search'),
				'method' => 'GET',
				'csrf_protection' => false,
				'attr' => [
					'class' => 'search',
				]
			]
		);
	}

	protected function _getTermsFromRequest()
	{
		$search = $this->get('http.request.master')->query->get('product_search');

		return ($search) ?: null;
	}

}