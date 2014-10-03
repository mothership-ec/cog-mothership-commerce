<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;

class Create extends Controller
{
	public function index()
	{
		return $this->render('Message:Mothership:Commerce::product:create', array(
			'form'  => $this->createForm($this->get('product.form.create')),
		));
	}

	public function process()
	{
		$form = $this->createForm($this->get('product.form.create'));
		$form->handleRequest();
		if ($form->isValid()) {
			$productCreator = $this->get('product.create');
			$unitCreator    = $this->get('product.unit.create');
			$stockManager   = $this->get('stock.manager');

			$product = $form->getData();

			foreach ($product->getUnits as $unit) {
				$unitCreator->create($unit);
			}

			$productCreator->create($product);

			return $this->render('Message:Mothership:Commerce::product:create', array(
				'form'  => $form,
			));
		}

		// $this->addFeedback('Invalid form, check fields');

		return $this->render('Message:Mothership:Commerce::product:create', array(
			'form'  => $form,
		));
	}
}
