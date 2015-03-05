<?php

namespace Message\Mothership\Commerce\Controller\Module;

use Message\Mothership\Commerce\Events;
use Message\Mothership\Commerce\Event;
use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\Unit\Unit;
use Message\Mothership\Commerce\Order;

use Message\Mothership\Commerce\Field\ProductUnitInStockOnlyChoiceType;

use Message\Mothership\CMS\Page\Content;

use Message\Cog\Controller\Controller;
use Message\Mothership\Commerce\Event\CurrencyChangeEvent;

class ProductSelector extends Controller
{
	protected $_availableUnits = array();

	/**
	 * Displays the product selector
	 * 
	 * @param  Product $product             Product for which to show the selector
	 * @param  array   $options             Product options
	 * @param  boolean $showVariablePricing Whether to show the price of the unit next
	 *                                      to its name in the dropdown, if unit prices
	 *                                      differ from product price.
	 *                                      
	 * @return Message\Cog\HTTP\Response    Response object
	 */
	public function index(Product $product, array $options = [], $showVariablePricing = false, $showQuantitySelector = false)
	{
		$options  = array_filter($options);
		$units    = $this->_getAvailableUnits($product, $options);
		$oosUnits = $this->_getOutOfStockUnits($units);

		if (count($units) === count($oosUnits)) {
			return $this->render('Message:Mothership:Commerce::product:product-selector-oos', array(
				'product' => $product,
				'units'   => $units,
			));
		}

		return $this->render('Message:Mothership:Commerce::product:product-selector', array(
			'product'     => $product,
			'units'       => $units,
			'form'        => $this->_getForm($product, $options, $showVariablePricing, $showQuantitySelector),
		));
	}

	public function process($productID)
	{
		$product = $this->get('product.loader')->getByID($productID);
		$form    = $this->_getForm($product);

		if ($form->isValid() && $data = $form->getFilteredData()) {
			$basket   = $this->get('basket');
			$unit     = $product->getUnit($data['unit_id']);

			$fail = false;
			for($i = 0; $i < $data['quantity']; ++$i) {
				$item     = new Order\Entity\Item\Item;
				$item->order         = $basket->getOrder();
				$item->stockLocation = $this->get('stock.locations')->get('web');
				$item->populate($unit);

				$item = $this->get('event.dispatcher')->dispatch(
					Events::PRODUCT_SELECTOR_PROCESS,
					new Event\ProductSelectorProcessEvent($form, $product, $item)
				)->getItem();

				if (!$basket->addItem($item)) {
					$this->addFlash('error', 'An item could not be added to your basket');
					$fail = true;
					break;
				}

			}

			if (!$fail) {
				$this->addFlash('success', 'Item added to basket');
			}

		}

		return $this->redirectToReferer();
	}

	protected function _getForm(Product $product, array $options = [], $showVariablePricing = false, $showQuantitySelector = false)
	{
		$form = $this->get('form')
			->setName('select_product')
			->setAction($this->generateUrl('ms.commerce.product.add.basket', ['productID' => $product->id]))
			->setMethod('post');

		$units    = $this->_getAvailableUnits($product, $options);
		$oosUnits = $this->_getOutOfStockUnits($units);
		$choices  = [];

		foreach ($units as $unit) {
			// Don't show option names that were passed as criteria to avoid weird-looking duplication
			$optionsToShow = ($options) ? array_diff_assoc($unit->options, $options) : $unit->options;

			$choices[$unit->id] = implode(', ', array_filter($optionsToShow));
		}

		// If there's only one unit available to choose, add it as a hidden field
		if (1 === count($choices)) {
			$form->add('unit_id', 'hidden', null, [
				'attr' => [
					'value' => key($choices),
				],
			]);
		// Otherwise, add a select box to select the unit
		} else {
			$form->add('unit_id', new ProductUnitInStockOnlyChoiceType, $this->trans('ms.commerce.product.selector.unit.label'), [
				'choices'      => $choices,
				'units'        => $units,
				'oos'          => array_keys($oosUnits),
				'empty_value'  => $this->trans('ms.commerce.product.selector.unit.label'),
				'show_pricing' => $showVariablePricing && $product->hasVariablePricing(),
			]);
		}

		if ($showQuantitySelector) {
			$form->add('quantity', 'choice', null, [
				'data'    => 1,
				'choices' => array_combine(range(1, 10), range(1, 10)),
			]);
		} else {
			$form->add('quantity', 'hidden', null, ['data' => 1]);
		}

		$form = $this->get('event.dispatcher')->dispatch(
			Events::PRODUCT_SELECTOR_BUILD,
			new Event\ProductSelectorEvent($form, $product)
		)->getForm();

		return $form;
	}

	protected function _getAvailableUnits(Product $product, array $options = [])
	{
		$key = md5(serialize(array($product->id, $options)));

		if (!array_key_exists($key, $this->_availableUnits)) {
			$this->_availableUnits[$key] = [];

			foreach ($product->getVisibleUnits() as $unit) {
				// Skip units that don't meet the options criteria, if set
				if ($options
				 && $options !== array_intersect_assoc($options, $unit->options)) {
					continue;
				}

				$this->_availableUnits[$key][$unit->id] = $unit;
			}
		}

		return $this->_availableUnits[$key];
	}

	protected function _getOutOfStockUnits(array $units)
	{
		$return = [];
		$locs   = $this->get('stock.locations');

		foreach ($units as $key => $unit) {
			if (!($unit instanceof Unit)) {
				throw new \InvalidArgumentException('Expected instance of Product\Unit\Unit');
			}

			if (1 > $unit->getStockForLocation($locs->getRoleLocation($locs::SELL_ROLE))) {
				$return[$key] = $unit;
			}
		}

		return $return;
	}
}