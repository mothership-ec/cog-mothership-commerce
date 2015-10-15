<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Cog\HTTP\Response;

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
			} else {
				return $this->render('Message:Mothership:Commerce::product:create-complete-modal', [
					'productID'  => $product->id,
				]);
			}

		}

		$response = new Response();
		$response
			->setStatusCode(400)
			->headers->set('Content-Type', 'text/html')
		;

		return $this->render('Message:Mothership:Commerce::product:create', [
			'form'  => $form,
		], $response);
	}
}
