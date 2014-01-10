<?php

namespace Message\Mothership\Commerce\Controller\Product;

use Message\Cog\Controller\Controller;
use Message\Cog\ValueObject\DateTimeImmutable;

class Delete extends Controller
{
	/**
	 * Delete a product
	 *
	 * @param  int 	$productID id of the product to be marked as deleted
	 */
	public function delete($productID)
	{
		// Check that the delete request has been sent
		if ($delete = $this->get('request')->get('delete')) {
			$product = $this->get('product.loader')->getByID($productID);

			if ($product = $this->get('product.delete')->delete($product)) {
				$this->addFlash(
					'success',
					sprintf(
						'%s was deleted. <a href="%s">Undo</a>',
						$product->name,
						$this->generateUrl('ms.commerce.product.restore', array('productID' => $product->id))
					)
				);
			} else {
				$this->addFlash('error', sprintf('%s could not be deleted.', $product->name));
			}

		}
		return $this->redirect($this->generateUrl('ms.commerce.product.dashboard'));
	}

	/**
	 * Restore a product that has been deleted.
	 *
	 * @param  int $productID	id of the product to be restored
	 */
	public function restore($productID)
	{
		$product = $this->get('product.loader')->includeDeleted(true)->getByID($productID);

		if ($this->get('product.delete')->restore($product)) {
			$this->addFlash('success', sprintf('%s was restored successfully', $product->name));
		} else {
			$this->addFlash('error', sprintf('%s could not be restored.', $product->name));
		}

		return $this->redirect($this->generateUrl('ms.commerce.product.dashboard'));
	}
}