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
use Message\Cog\ValueObject\DateTimeImmutable;

class ProductSelector extends Controller
{
	protected $_availableUnits = array();

	/**
	 * Render the product selector form.
	 *
	 * @todo Remove deprecated $showVariablePricing in favour of the setting of
	 *       the same name in the next major version.
	 *
	 * @param  Product $product
	 * @param  array   $options  Product options to display, passing an empty
	 *                           array defaults to all available options.
	 * @param  array   $settings Settings for the product selector.
	 *                           - 'showNotificationForm', set to true to
	 *                              display the notification form when the
	 *                              product has out of stock options.
	 *                           - 'collapseFullyOutOfStock', set to true to
	 *                              only show one input when all options are
	 *                              out of stock.
	 *                           - 'showVariablePricing', set to true to show
	 *                              the price of the unit next to it's name in
	 *                              the option dropdown if unit prices differ
	 *                              to product price.
	 * @param  boolean $showVariablePricing See setting of the same name. This
	 *                                      is here for backwards-compatibility
	 *                                      and should be removed in the next
	 *                                      major version
	 * @return \Message\Cog\HTTP\Response
	 */
	public function index(Product $product, array $options = [], array $settings = [], $showVariablePricing = false)
	{
		$settings += [
			'showNotificationForm'    => false,
			'collapseFullyOutOfStock' => false,
			'showVariablePricing'     => $showVariablePricing,
		];

		$options  = array_filter($options);
		$units    = $this->_getAvailableUnits($product, $options);
		$oosUnits = $this->_getOutOfStockUnits($units);

		$notificationForm = $this->_getReplenishedNotificationForm(
			$product, $units, $oosUnits, $settings
		);

		return $this->render('Message:Mothership:Commerce::product:product-selector', [
			'product'                     => $product,
			'units'                       => $units,
			'form'                        => $this->_getForm($product, $options, $settings['showVariablePricing']),
			'replenishedNotificationForm' => $notificationForm,
		]);
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

		if (! isset($data['email']) or empty($data['email']) or ! isset($data['product_units']) or empty($data['product_units'])) {
			$this->addFlash('error', "Please fill all required fields");
			return $this->redirectToReferer();
		}

		if (! is_array($data['product_units'])) $data['product_units'] = array($data['product_units']);

		// Add a separate notification for each unit.
		foreach ($data['product_units'] as $unitID) {
			$notification = new Stock\Notification\Replenished\Notification;
			$notification->email = $data['email'];
			$notification->unitID = $unitID;

			if ($user = $this->get('user.loader')->getByEmail($notification->email)) {
				$notification->authorship->create(new DateTimeImmutable, $user->id);
			}

			$this->get('stock.notification.replenished.create')->create($notification);
		}

		// Only add a single flash message even if multiple units are selected.
		$this->addFlash('success', $this->trans('ms.commerce.product.notification.replenished.success', [
				'%email%' => $data['email']
			])
		);

		return $this->redirectToReferer();
	}

	protected function _getForm(Product $product, array $options = [], $showVariablePricing = false)
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
	 * @param  Product $product
	 * @param  array   $units
	 * @param  array   $oosUnits
	 * @param  array   $settings  See index()
	 * @return Message\Cog\Form\Handler
	 */
	protected function _getReplenishedNotificationForm($product, $units, $oosUnits, $settings)
	{
		// If there are no units to display, load all units for the product.
		if (0 == count($units)) {
			$loader = $this->get('product.unit.loader');
			$loader->includeOutOfStock(true);
			$loader->includeInvisible(true);
			$oosUnits = $loader->getByProduct($product);
		}

		if (0 == count($oosUnits)) return false;

		$email = (! $this->get('user.current') instanceof AnonymousUser)
		       ? $this->get('user.current')->email
		       : '';

		$form = $this->createForm($this->get('stock.notification.replenished.form'), [
			'product'                 => $product,
			'units'                   => $units,
			'oosUnits'                => $oosUnits,
			'email'                   => $email,
			'collapseFullyOutOfStock' => $settings['collapseFullyOutOfStock'],
		], [
			'action' => $this->generateUrl('ms.commerce.product.stock.notification.replenished.signup'),
			'method' => 'post',
		]);

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