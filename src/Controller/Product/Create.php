<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;

class Create extends Controller
{
	public function index()
	{
		return $this->render('Message:Mothership:Commerce::product:create', [
			'form'  => $this->createForm($this->get('product.form.create')),
		]);
	}

	public function process()
	{
		$form = $this->createForm($this->get('product.form.create'));
		$form->handleRequest();
		if ($form->isValid()) {
			$productCreator = $this->get('product.create');
			$unitCreator    = $this->get('product.unit.create');
			$stockManager   = $this->get('stock.manager');
			$stockLocations = $this->get('stock.locations');

			$product = $form->getData();
			$product->authorship->create(new DateTimeImmutable, $this->get('user.current'));

			foreach ($product->getUnits() as $unit) {
				$unit = $unitCreator->create($unit);
				$stockManager->setReason('stock added'); // TODO

				foreach($unit->getStockArray() as $location => $stock) {
					$stockManager->set(
							$unit->id,
							$stockLocations->get($location),
							$stock
						);
				}

				if (!$stockManager->commit()) {
					$this->addFlash('error', 'Could not update stock');
				}
			}

			$productCreator->create($product);

			// return $this->render('Message:Mothership:Commerce::product:create', [
			// 	'form'  => $form,
			// ]);
		}

		return $this->render('Message:Mothership:Commerce::product:create', [
			'form'  => $form,
		]);
	}
}
