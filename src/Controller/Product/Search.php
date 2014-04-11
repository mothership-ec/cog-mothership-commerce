<?php

namespace Message\Mothership\Commerce\Controller\Product;

use \Message\Cog\Controller\Controller;

class Search extends Controller
{
	public function index()
	{
		$form = $this->getForm();

		return $this->render('Message:Mothership:Commerce::product:search', array(
			'form' => $form,
		));
	}

	public function process()
	{
		$form = $this->getForm();

		if (!$form->isValid() || !$data = $form->getFilteredData()) {
			// Add error
			return $this->redirectToReferer();
		}

		$products = $this->get('product.loader')->getBySearchTerms($data['terms'], 50);

		return $this->render('Message:Mothership:Commerce::product:search-results', array(
			'product' => $products,
		));
	}

	public function getForm()
	{
		$defaults = array();
		$search = $this->get('http.request.master')->query->get('search');

		if (isset($search['terms']) && $search['terms']) {
			$defaults = array('terms' => $search['terms']);
		}

		$form = $this->get('form')
			->setName('search')
			->setMethod('GET')
			->setAction($this->generateUrl('ms.commerce.product.search'))
			->addOptions(
				array(
					'csrf_protection' => false,
					'attr' => array(
						'class'=>'search',
					)
				))
			->setDefaultValues($defaults);

		$form->add('terms', 'search', $this->trans('ms.commerce.product.search.label'), array(
			'attr' => array(
				'placeholder' => 'Search content&hellip;'
			)
		));

		return $form;
	}

}