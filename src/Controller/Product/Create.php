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
			$stockLocations = $this->get('stock.locations');
			$stockManager   = $this->get('stock.manager');

			$product = $form->getData();
			$product->authorship->create(new DateTimeImmutable, $this->get('user.current'));
			// save overwrite product with saved product to get new id
			$product = $productCreator->create($product);
			// save prices
			$product = $this->get('product.edit')->savePrices($product);

			$stockManager->setReason($this->get('stock.movement.reasons')->get('new_order'));

			foreach ($product->getAllUnits() as $unit) {

				$unit->authorship->create(new DateTimeImmutable, $this->get('user.current'));
				$unit = $unitCreator->create($unit);
				d($unit->options);
				foreach($unit->getStockArray() as $location => $stock) {
					$stockManager->set(
							$unit,
							$stockLocations->get($location),
							$stock
						);
				}

			}

			if (!$stockManager->commit()) {
				$this->addFlash('error', 'Could not update stock');
			}


			return $this->render('Message:Mothership:Commerce::product:create', [
				'form'  => $form,
			]);
		}

		return $this->render('Message:Mothership:Commerce::product:create', [
			'form'  => $form,
		]);
	}
}
