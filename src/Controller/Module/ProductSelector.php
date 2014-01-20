<?php

namespace Message\Mothership\Commerce\Controller\Module;

use Message\Mothership\Commerce\Events;
use Message\Mothership\Commerce\Event;
use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\Unit\Unit;
use Message\Mothership\Commerce\Product\Stock;
use Message\Mothership\Commerce\Order;

use Message\Mothership\Commerce\Field\ProductUnitInStockOnlyChoiceType;

use Message\Mothership\CMS\Page\Content;

use Message\Cog\Controller\Controller;

class ProductSelector extends Controller
{
	protected $_availableUnits = array();

	public function index(Product $product, array $options = array(), $collapseFullyOos = false)
	{
		$options  = array_filter($options);
		$units    = $this->_getAvailableUnits($product, $options);
		$oosUnits = $this->_filterInStockUnits($units);

		if (count($units) === count($oosUnits)) {
			return $this->render('Message:Mothership:Commerce::product:product-selector-oos', array(
				'product' => $product,
				'units'   => $units,
				'replenishedNotificationForm' => $this->_getReplenishedNotificationForm($product, $oosUnits, $collapseFullyOos)
			));
		}

		return $this->render('Message:Mothership:Commerce::product:product-selector', array(
			'product' => $product,
			'units'   => $units,
			'form'    => $this->_getForm($product, $options),
			'replenishedNotificationForm' => count($oosUnits) ?
				$this->_getReplenishedNotificationForm($product, $oosUnits, false) :
				false
		));
	}

	public function process($productID)
	{
		$product = $this->get('product.loader')->getByID($productID);
		$form    = $this->_getForm($product);

		if ($form->isValid() && $data = $form->getFilteredData()) {
			$basket   = $this->get('basket');
			$unit     = $product->getUnit($data['unit_id']);
			$item     = new Order\Entity\Item\Item;

			$item->order         = $basket->getOrder();
			$item->stockLocation = $this->get('stock.locations')->get('web');
			$item->populate($unit);

			$item = $this->get('event.dispatcher')->dispatch(
				Events::PRODUCT_SELECTOR_PROCESS,
				new Event\ProductSelectorProcessEvent($form, $product, $item)
			)->getItem();

			if ($basket->addItem($item)) {
				$this->addFlash('success', 'The item has been added to your basket');
			}
		}

		return $this->redirectToReferer();
	}

	/**
	 * Process the replenished notification email signup. This does not use the
	 * usual `$form->isValid()` method since we do not know the units to pass
	 * into the form builder.
	 *
	 * @return Message\Cog\HTTP\RedirectResponse
	 */
	public function processReplenishedNotificationSignup()
	{
		$data = $this->get('request')->request->get('replenished_notification');

		if (! isset($data['email']) or empty($data['email']) or ! isset($data['units']) or empty($data['units'])) {
			$this->addFlash('error', "Please fill all required fields");
			return $this->redirectToReferer();
		}

		if (! is_array($data['units'])) $data['units'] = array($data['units']);

		// Add a separate notification for each unit.
		foreach ($data['units'] as $unitID) {
			$notification = new Stock\Notification\Replenished\Notification;
			$notification->email = $data['email'];
			$notification->unitID = $unitID;

			$this->get('stock.notification.replenished.create')->create($notification);
		}

		// Only add a single flash message even if multiple units are selected.
		$this->addFlash('success', sprintf(
			'A notification will be sent to <em>%s</em> when this product is back in stock',
			$data['email']
		));

		return $this->redirectToReferer();
	}

	protected function _getForm(Product $product, array $options = array())
	{
		$form = $this->get('form')
			->setName('select_product')
			->setAction($this->generateUrl('ms.commerce.product.add.basket', array('productID' => $product->id)))
			->setMethod('post');

		$units    = $this->_getAvailableUnits($product, $options);
		$oosUnits = $this->_filterInStockUnits($units);
		$choices  = array();

		foreach ($units as $unit) {
			// Don't show option names that were passed as criteria to avoid weird-looking duplication
			$optionsToShow = ($options) ? array_diff_assoc($unit->options, $options) : $unit->options;

			$choices[$unit->id] = implode(', ', array_filter($optionsToShow));
		}

		// If there's only one unit available to choose, add it as a hidden field
		if (1 === count($choices)) {
			$form->add('unit_id', 'hidden', null, array(
				'attr' => array(
					'value' => key($choices),
				),
			));
		// Otherwise, add a select box to select the unit
		} else {
			$form->add('unit_id', new ProductUnitInStockOnlyChoiceType, $this->trans('ms.commerce.product.selector.unit.label'), array(
				'choices'     => $choices,
				'oos'         => array_keys($oosUnits),
				'empty_value' => $this->trans('ms.commerce.product.selector.unit.label')
			));
		}

		$form = $this->get('event.dispatcher')->dispatch(
			Events::PRODUCT_SELECTOR_BUILD,
			new Event\ProductSelectorEvent($form, $product)
		)->getForm();

		return $form;
	}

	/**
	 * Get the stock replenished notification email signup form. If a single
	 * unit is passed this is added a hidden field, for multiple units the user
	 * is able to choose from a list as to which they would like notification(s)
	 * for.
	 *
	 * @param  array[Unit] $units Out of stock units to choose from.
	 * @return Message\Cog\Form\Handler
	 */
	protected function _getReplenishedNotificationForm($product, $units, $collapse = false)
	{
		$form = $this->get('form')
			->setName('replenished_notification')
			->setAction($this->generateUrl('ms.commerce.product.stock.notification.replenished.signup'))
			->setMethod('post');

		// If there are no units to display, load all units for the product.
		if (count($units) == 0) {
			$loader = $this->get('product.unit.loader');
			$loader->includeOutOfStock(true);
			$loader->includeInvisible(true);
			$units = $loader->getByProduct($product);
		}

		if (count($units) == 1) {
			$unit = array_shift($units);
			$form->add('units', 'hidden', false, array(
				'data' => $unit->id
			));
		}
		elseif (count($units) > 1) {
			foreach ($units as $unit) {
				$choices[$unit->id] = implode(',', $unit->options);
			}

			if ($collapse) {
				// @fix: The label ' ' is a dirty hack to stop the label showing for
				// these hidden fields.
				$form->add('units', 'collection', ' ', array(
					'type' => 'hidden',
					'data' => array_keys($choices)
				));
			}
			else {
				$form->add('units', 'choice', 'Choose the options you are interested in', array(
					'choices' => $choices,
					'expanded' => true,
					'multiple' => true
				));
			}
		}

		$form->add('email', 'text', 'Email', array(
			'data' => (! $this->get('user.current') instanceof AnonymousUser) ? $this->get('user.current')->email : ''
		));

		return $form;
	}

	protected function _getAvailableUnits(Product $product, array $options = array())
	{
		$key = md5(serialize(array($product, $options)));

		if (!array_key_exists($key, $this->_availableUnits)) {
			$this->_availableUnits[$key] = array();

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

	protected function _filterInStockUnits(array $units)
	{
		$return = array();
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