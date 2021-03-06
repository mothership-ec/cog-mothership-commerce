<?php

namespace Message\Mothership\Commerce\Bootstrap;

use Message\Mothership\Commerce;
use Message\Mothership\Commerce\Order\Statuses as OrderStatuses;
use Message\Mothership\Commerce\Product\Stock\Movement\Reason;
use Message\Mothership\Commerce\Order\Transaction\Types as TransactionTypes;
use Message\Cog\DB\Entity\EntityLoaderCollection;

use Message\User\AnonymousUser;

use Message\Cog\Bootstrap\ServicesInterface;
use Message\Mothership\Report\Report\Collection as ReportCollection;
use Message\Mothership\Commerce\Product\Tax\TaxManager;
use Message\Mothership\Commerce\Product\Tax\Strategy;
use Message\Mothership\Commerce\Pagination\OrderAdapter;

class Services implements ServicesInterface
{
	public function registerServices($services)
	{
		$this->registerEmails($services);
		$this->registerProductPageMapper($services);
		$this->registerStatisticsDatasets($services);
		$this->registerReports($services);
		$this->setupCurrencies($services);

		$services['order'] = $services->factory(function($c) {
			$order = new Commerce\Order\Order($c['order.entities']);
			$order->taxable = true; // default orders to taxable

			return $order;
		});

		$services->extend('form.factory.builder', function($factory, $c) {
			$factory->addExtension(new Commerce\Form\Extension\CommerceExtension($c['currency.supported'], $c['translator'], $c['product.price.types'], $c['product.types']));

			return $factory;
		});

		$services['commerce.form.order.simple_search'] = $services->factory(function($c) {
			return new Commerce\Form\Order\SimpleSearch;
		});

		$services['basket.order'] = $services->factory(function($c) {
			if (!$c['http.session']->get('basket.order')) {
				$order             = $c['order'];
				$order->locale     = $c['locale']->getId();
				$order->currencyID = $c['currency'];
				$order->type       = 'web';

				if ($c['user.current']
				&& !($c['user.current'] instanceof AnonymousUser)) {
					$order->user = $c['user.current'];
				}

				$c['http.session']->set('basket.order', $order);
			}

			return $c['http.session']->get('basket.order');
		});

		$services['basket'] = $services->factory(function($c) {
			$assembler = $c['order.assembler'];
			$assembler->setOrder($c['basket.order']);

			return $assembler;
		});

		$services['order.entities'] = $services->factory(function($c) {
			return array(
				'addresses'  => new Commerce\Order\Entity\CollectionOrderLoader(
					new Commerce\Order\Entity\Address\Collection,
					new Commerce\Order\Entity\Address\Loader($c['db.query'])
				),
				'discounts'  => new Commerce\Order\Entity\CollectionOrderLoader(
					new Commerce\Order\Entity\Discount\Collection,
					new Commerce\Order\Entity\Discount\Loader($c['db.query'])
				),
				'dispatches' => new Commerce\Order\Entity\CollectionOrderLoader(
					new Commerce\Order\Entity\Collection,
					new Commerce\Order\Entity\Dispatch\Loader($c['db.query'], $c['order.dispatch.methods'])
				),
				'documents'  => new Commerce\Order\Entity\CollectionOrderLoader(
					new Commerce\Order\Entity\Collection,
					new Commerce\Order\Entity\Document\Loader($c['db.query'])
				),
				'items'      => new Commerce\Order\Entity\CollectionOrderLoader(
					new Commerce\Order\Entity\Item\Collection,
					new Commerce\Order\Entity\Item\Loader($c['db.query'], $c['order.item.status.loader'], $c['stock.locations'])
				),
				'notes'      => new Commerce\Order\Entity\CollectionOrderLoader(
					new Commerce\Order\Entity\Collection,
					new Commerce\Order\Entity\Note\Loader($c['db.query'], $c['event.dispatcher'])
				),
				'payments'   => new Commerce\Order\Entity\CollectionOrderLoader(
					new Commerce\Order\Entity\Collection,
					new Commerce\Order\Entity\Payment\Loader($c['db.query'], $c['payment.loader'])
				),
				'refunds'    => new Commerce\Order\Entity\CollectionOrderLoader(
					new Commerce\Order\Entity\Collection,
					new Commerce\Order\Entity\Refund\Loader($c['db.query'], $c['refund.loader'])
				),
			);
		});

		$services['order.assembler'] = $services->factory(function($c) {
			$order = $c['order'];

			$order->locale     = $c['locale']->getId();
			$order->currencyID = $c['currency'];

			$assembler = new Commerce\Order\Assembler(
				$order,
				$c['event.dispatcher'],
				$c['stock.locations']->getRoleLocation($c['stock.locations']::SELL_ROLE)
			);

			$assembler->setEntityTemporaryIdProperty('addresses', 'type');
			$assembler->setEntityTemporaryIdProperty('discounts', 'code');

			return $assembler;
		});

		// Order decorators
		$services['order.loader'] = $services->factory(function($c) {
			return new Commerce\Order\Loader(
				$c['db.query'],
				$c['user.loader'],
				$c['order.statuses'],
				$c['order.item.statuses'],
				$c['order.entities'],
				$c['db.query.builder.factory']
			);
		});

		$services['order.create'] = $services->factory(function($c) {
			return new Commerce\Order\Create(
				$c['db.transaction'],
				$c['order.loader'],
				$c['event.dispatcher'],
				$c['user.current'],
				array(
					'addresses' => $c['order.address.create'],
					'discounts' => $c['order.discount.create'],
					'items'     => $c['order.item.create'],
					'notes'     => $c['order.note.create'],
					'payments'  => $c['order.payment.create'],
					'refunds'   => $c['order.refund.create'],
				)
			);
		});

		$services['order.pagination.adapter'] = function($c) {
			return new OrderAdapter($c['order.loader']);
		};

		$services['order.delete'] = $services->factory(function($c) {
			return new Commerce\Order\Delete($c['db.transaction'], $c['event.dispatcher'], $c['user.current']);
		});

		$services['order.edit'] = $services->factory(function($c) {
			return new Commerce\Order\Edit(
				$c['db.transaction'],
				$c['event.dispatcher'],
				$c['order.statuses'],
				$c['user.current']
			);
		});

		// Order forms
		$services['order.form.cancel'] = function($c) {
			return new Commerce\Form\Order\Cancel(
				$c['stock.locations']->getRoleLocation($c['stock.locations']::SELL_ROLE),
				$c['user.loader']->getUserPassword($c['user.current']),
				$c['user.password_hash'],
				$c['translator']
			);
		};

		// Order address entity
		$services['order.address.loader'] = $services->factory(function($c) {
			return $c['order.loader']->getEntityLoader('addresses');
		});

		$services['order.address.create'] = $services->factory(function($c) {
			return new Commerce\Order\Entity\Address\Create($c['db.query'], $c['order.address.loader'], $c['user.current']);
		});

		// Order item entity
		$services['order.item.loader'] = $services->factory(function($c) {
			return $c['order.loader']->getEntityLoader('items');
		});

		$services['order.item.create'] = $services->factory(function($c) {
			return new Commerce\Order\Entity\Item\Create($c['db.transaction'], $c['order.item.loader'], $c['event.dispatcher'], $c['user.current']);
		});

		$services['order.item.delete'] = $services->factory(function($c) {
			return new Commerce\Order\Entity\Item\Delete($c['db.query'], $c['user.current']);
		});

		$services['order.item.edit'] = $services->factory(function($c) {
			return new Commerce\Order\Entity\Item\Edit($c['db.transaction'], $c['event.dispatcher'], $c['order.item.statuses'], $c['user.current']);
		});

		// Order discount entity
		$services['order.discount.loader'] = $services->factory(function($c) {
			return $c['order.loader']->getEntityLoader('discounts');
		});

		$services['order.discount.create'] = $services->factory(function($c) {
			return new Commerce\Order\Entity\Discount\Create($c['db.query'], $c['order.discount.loader'], $c['user.current']);
		});

		// Order dispatch entity
		$services['order.dispatch.loader'] = $services->factory(function($c) {
			return $c['order.loader']->getEntityLoader('dispatches');
		});

		$services['order.dispatch.create'] = $services->factory(function($c) {
			return new Commerce\Order\Entity\Dispatch\Create($c['db.transaction'], $c['order.dispatch.loader'], $c['user.current']);
		});

		$services['order.dispatch.delete'] = $services->factory(function($c) {
			return new Commerce\Order\Entity\Dispatch\Delete($c['db.query'], $c['user.current']);
		});

		$services['order.dispatch.edit'] = $services->factory(function($c) {
			return new Commerce\Order\Entity\Dispatch\Edit($c['db.transaction'], $c['user.current'], $c['event.dispatcher']);
		});

		// Order document entity
		$services['order.document.loader'] = $services->factory(function($c) {
			return $c['order.loader']->getEntityLoader('documents');
		});

		$services['order.document.create'] = $services->factory(function($c) {
			return new Commerce\Order\Entity\Document\Create(
				$c['db.query'],
				$c['order.document.loader'],
				$c['user.current']
			);
		});

		// Order item status
		$services['order.item.status.loader'] = $services->factory(function($c) {
			return new Commerce\Order\Entity\Item\Status\Loader(
				$c['db.query'],
				$c['order.item.statuses']
			);
		});

		// Order payment entity
		$services['order.payment.loader'] = $services->factory(function($c) {
			return $c['order.loader']->getEntityLoader('payments');
		});

		$services['order.payment.create'] = $services->factory(function($c) {
			return new Commerce\Order\Entity\Payment\Create(
				$c['db.transaction'],
				$c['payment.create'],
				$c['order.payment.loader'],
				$c['event.dispatcher']
			);
		});

		$services['order.payment.delete'] = $services->factory(function($c) {
			return new Commerce\Order\Entity\Payment\Delete($c['payment.delete']);
		});

		// Order refund entity
		$services['order.refund.loader'] = $services->factory(function($c) {
			return $c['order.loader']->getEntityLoader('refunds');
		});

		$services['order.refund.create'] = $services->factory(function($c) {
			return new Commerce\Order\Entity\Refund\Create(
				$c['db.transaction'],
				$c['refund.create'],
				$c['order.refund.loader'],
				$c['event.dispatcher']
			);
		});

		$services['order.refund.delete'] = $services->factory(function($c) {
			return new Commerce\Order\Entity\Refund\Delete($c['refund.delete']);
		});

		// Order note entity
		$services['order.note.loader'] = $services->factory(function($c) {
			return $c['order.loader']->getEntityLoader('notes');
		});

		$services['order.note.create'] = $services->factory(function($c) {
			return new Commerce\Order\Entity\Note\Create(
				$c['db.query'],
				$c['order.note.loader'],
				$c['user.current'],
				$c['event.dispatcher']);
		});

		// Available despatch methods
		$services['order.dispatch.methods'] = function($c) {
			return new Commerce\Order\Entity\Dispatch\MethodCollection;
		};

		// Dispatch method selector
		$services['order.dispatch.method.selector'] = function($c) {
			return new Commerce\Order\Entity\Dispatch\MethodSelector($c['order.dispatch.methods']);
		};

		// Available order & item statuses
		$services['order.statuses'] = function($c) {
			return new Commerce\Order\Status\Collection(array(
				new Commerce\Order\Status\Status(OrderStatuses::PAYMENT_PENDING,      'Payment Pending'),
				new Commerce\Order\Status\Status(OrderStatuses::CANCELLED,            'Cancelled'),
				new Commerce\Order\Status\Status(OrderStatuses::AWAITING_DISPATCH,    'Awaiting Dispatch'),
				new Commerce\Order\Status\Status(OrderStatuses::PROCESSING,           'Processing'),
				new Commerce\Order\Status\Status(OrderStatuses::PARTIALLY_DISPATCHED, 'Partially Dispatched'),
				new Commerce\Order\Status\Status(OrderStatuses::PARTIALLY_RECEIVED,   'Partially Received'),
				new Commerce\Order\Status\Status(OrderStatuses::DISPATCHED,           'Dispatched'),
				new Commerce\Order\Status\Status(OrderStatuses::RECEIVED,             'Received'),
			));
		};

		$services['order.item.statuses'] = function($c) {
			return new Commerce\Order\Status\Collection(array(
				new Commerce\Order\Status\Status(OrderStatuses::CANCELLED,         'Cancelled'),
				new Commerce\Order\Status\Status(OrderStatuses::AWAITING_DISPATCH, 'Awaiting Dispatch'),
				new Commerce\Order\Status\Status(OrderStatuses::DISPATCHED,        'Dispatched'),
				new Commerce\Order\Status\Status(OrderStatuses::RECEIVED,          'Received'),
			));
		};

		$services['order.specification.cancellable'] = function($c) {
			return new Commerce\Order\Specification\OrderCanBeCancelledSpecification;
		};

		$services['order.item.specification.cancellable'] = function($c) {
			return new Commerce\Order\Entity\Item\ItemCanBeCancelledSpecification;
		};

		$services['order.listener.assembler.stock_check'] = function($c) {
			return new Commerce\Order\EventListener\Assembler\StockCheckListener('web');
		};

		// Available transaction types
		$services['order.transaction.types'] = function($c) {
			return array(
				TransactionTypes::ORDER               => 'Order',
				TransactionTypes::CONTRACT_INITIATION => 'Contract Initiation',
				TransactionTypes::CONTRACT_PAYMENT    => 'Contract Payment',
			);
		};

		$services['order.transaction.loader'] = function($c) {
			$orderLoader        = $c['order.loader'];
			$itemLoader         = $c['order.item.loader'];
			$paymentLoader      = $c['payment.loader'];
			$refundLoader       = $c['refund.loader'];
			$orderPaymentLoader = $c['order.payment.loader'];
			$orderRefundLoader  = $c['order.refund.loader'];

			$orderLoader->includeDeleted(true);
			$itemLoader->includeDeleted(true);
			$paymentLoader->includeDeleted(true);
			$refundLoader->includeDeleted(true);
			$orderPaymentLoader->includeDeleted(true);
			$orderRefundLoader->includeDeleted(true);

			return new Commerce\Order\Transaction\Loader($c['db.query'], array(
				Commerce\Order\Order::RECORD_TYPE                  => $orderLoader,
				Commerce\Order\Entity\Item\Item::RECORD_TYPE       => $itemLoader,
				Commerce\Refund\Refund::RECORD_TYPE                => $refundLoader,
				Commerce\Payment\Payment::RECORD_TYPE              => $paymentLoader,
				Commerce\Order\Entity\Refund\Refund::RECORD_TYPE   => $orderRefundLoader,
				Commerce\Order\Entity\Payment\Payment::RECORD_TYPE => $orderPaymentLoader,
			));
		};

		$services['order.transaction.create'] = function($c) {
			return new Commerce\Order\Transaction\Create($c['db.transaction'], $c['order.transaction.loader'], $c['event.dispatcher'], $c['user.current']);
		};

		$services['order.transaction.edit'] = function($c) {
			return new Commerce\Order\Transaction\Edit($c['db.transaction'], $c['user.current']);
		};

		$services['order.transaction.void'] = function($c) {
			return new Commerce\Order\Transaction\Void(
				$c['db.transaction'],
				$c['event.dispatcher'],
				$c['user.current']
			);
		};

		// Product
		$services['product'] = $services->factory(function($c) {
			return new Commerce\Product\Product($c['locale'], $c['product.price.types'], $c['currency'], $c['product.tax.strategy']);
		});

		$services['product.unit'] = $services->factory(function($c) {
			return new Commerce\Product\Unit\Unit($c['locale'], $c['product.price.types'], $c['currency']);
		});

		$services['product.price.types'] = function($c) {
			return array(
				'retail',
				'rrp',
				'cost',
			);
		};

		/**
		 * @deprecated  use currency.supported
		 */
		$services['product.price.currency_IDs'] = function($c) {
			return $c['currency.supported'];
		};

		$services['product.entity_loaders'] = $services->factory(function($c) {
			return 	new EntityLoaderCollection([
				'units'  => new Commerce\Product\Unit\Loader(
					$c['db.query.builder.factory'],
					$c['locale'],
					$c['product.price.types'],
					$c['currency']
				),
				'images' => new Commerce\Product\Image\Loader(
					$c['db.query'],
					$c['file_manager.file.loader']
				),
				'details' => new Commerce\Product\Type\DetailLoader(
					$c['db.query'],
					$c['field.factory'],
					$c['product.types']
				),
				'prices' => new Commerce\Product\Price\PriceLoader(
					$c['db.query'],
					$c['locale']
				),
				'taxes' => new Commerce\Product\Tax\TaxLoader(
					$c['product.tax.resolver'],
					$c['product.tax.address']
				),
			]);
		});

		// Product loader should not be a singleton as 'includeDeleted' needs to be reset to false on every
		// instanciation
		$services['product.loader'] = $services->factory(function($c) {
			return new Commerce\Product\Loader(
				$c['db.query'],
				$c['locale'],
				$c['file_manager.file.loader'],
				$c['product.types'],
				$c['product.detail.loader'],
				$c['product.entity_loaders'],
				$c['product.price.types'],
				$c['currency'],
				$c['product.tax.strategy'],
				$c['product.cache']
			);
		});

		$services['product.cache'] = function($c) {
			return new Commerce\Product\Collection;
		};

		$services['product.searcher'] = $services->factory(function($c) {
			return new Commerce\Product\Searcher(
				$c['db.query'],
				$c['product.loader'],
				3
			);
		});

		$services['product.create'] = $services->factory(function($c) {
			$create = new Commerce\Product\Create($c['db.query'],
				$c['locale'],
				$c['user.current'],
				$c['product.price.types'],
				$c['product.price.currency_IDs']
			);

			$create->setDefaultTaxStrategy($c['product.tax.strategy']);

			return $create;
		});

		$services['product.form.data_transform'] = $services->factory(function($c) {
			return new Commerce\Product\Form\DataTransform\ProductTransform(
				$c['locale'],
				$c['stock.default.location'],
				$c['product.price.types'],
				$c['product.types'],
				$c['currency.default'],
				$c['product.tax.strategy']
			);
		});

		$services['product.form.create'] = $services->factory(function($c){
			return new Commerce\Product\Form\Create($c['translator'], $c['product.types'], $c['product.form.data_transform']);
		});

		$services['product.edit'] = $services->factory(function($c) {
			return new Commerce\Product\Edit($c['db.transaction'], $c['locale'], $c['user.current'], $c['event.dispatcher']);
		});

		$services['product.delete'] = $services->factory(function($c) {
			return new Commerce\Product\Delete($c['db.query'], $c['user.current']);
		});

		$services['product.image.types'] = function($c) {
			return new Commerce\Product\Image\TypeCollection(array(
				'default' => 'Default',
			));
		};

		$services['product.image.create'] = $services->factory(function($c) {
			return new Commerce\Product\Image\Create($c['db.transaction'], $c['user.current']);
		});

		$services['product.image.delete'] = $services->factory(function($c) {
			return new Commerce\Product\Image\Delete($c['db.transaction'], $c['user.current']);
		});

		$services['product.image.loader'] = $services->factory(function($c) {
			return $c['product.loader']->getEntityLoader('images');
		});

		$services['product.image.form.delete'] = $services->factory(function($c) {
			return new Commerce\Form\Product\Image\Delete;
		});

		$services['product.image.assignor'] = function($c) {
			return new Commerce\Product\Image\Assignor(
				$c['file_manager.file.loader'],
				$c['locale']
			);
		};

		$services['product.unit.loader'] = $services->factory(function($c) {
			return $c['product.loader']->getEntityLoader('units');
		});

		$services['product.unit.edit'] = $services->factory(function($c) {
			return new Commerce\Product\Unit\Edit($c['db.query'], $c['product.unit.loader'], $c['user.current'], $c['locale']);
		});

		$services['product.unit.create'] = $services->factory(function($c) {
			return new Commerce\Product\Unit\Create($c['db.query'], $c['user.current'], $c['locale'], $c['event.dispatcher']);
		});

		$services['product.unit.delete'] = $services->factory(function($c) {
			return new Commerce\Product\Unit\Delete($c['db.query'], $c['user.current']);
		});

		// CSV upload
		$services['product.field_crawler'] = function($c) {
			return new Commerce\Product\Type\FieldCrawler($c['product.types']);
		};

		$services['product.upload.csv_heading'] = function($c) {
			return new \Message\Cog\FileDownload\Csv\Row($c['product.upload.heading_builder']->getColumns());
		};

		$services['product.upload.heading_builder'] = function($c) {
			return new Commerce\Product\Upload\HeadingBuilder(
				$c['product.field_crawler'], $c['translator'], $c['currency.supported'], $c['currency']);
		};

		$services['product.upload.heading_keys'] = function($c) {
			$headingKeys = new Commerce\Product\Upload\HeadingKeys($c['product.upload.heading_builder']->getColumns(), $c['currency.supported']);
			$headingKeys->setColumnDependencies($c['product.upload.heading_builder']->getColumnDependencies());

			return $headingKeys;
		};

		$services['product.upload.csv_template'] = function($c) {
			return new \Message\Cog\FileDownload\Csv\Table([
				$c['product.upload.csv_heading'],
			]);
		};

		$services['product.upload.csv_download'] = function($c) {
			return new \Message\Cog\FileDownload\Csv\Download($c['product.upload.csv_template']);
		};

		$services['product.upload.validator'] = function($c) {
			return new Commerce\Product\Upload\Validate($c['product.upload.heading_keys'], $c['product.field_crawler']);
		};

		$services['product.upload.filter'] = function($c) {
			return new Commerce\Product\Upload\Filter($c['product.upload.heading_keys']);
		};

		$services['product.upload.csv_converter'] = function($c) {
			return new Commerce\Product\Upload\Csv\CsvToArrayConverter;
		};

		$services['product.upload.product_builder'] = $services->factory(function($c) {
			return new Commerce\Product\Upload\ProductBuilder(
				$c['product.upload.heading_keys'],
				$c['product.upload.validator'],
				$c['product.types'],
				$c['product.field_crawler'],
				$c['user.current'],
				$c['product'],
				$c['locale'],
				$c['currency.supported'],
				$c['currency'],
				$c['cfg']->merchant->address->countryID
			);
		});

		$services['product.upload.unit_builder'] = $services->factory(function($c) {
			return new Commerce\Product\Upload\UnitBuilder(
				$c['product.upload.heading_keys'],
				$c['product.upload.validator'],
				$c['locale'],
				$c['user.current'],
				$c['currency.supported'],
				$c['product.unit'],
				$c['product.upload.unit_stock']
			);
		});

		$services['product.upload.unique_sorter'] = function($c) {
			return new Commerce\Product\Upload\UniqueProductSorter($c['product.upload.heading_keys']);
		};

		$services['product.upload.create_dispatcher'] = $services->factory(function($c) {
			return new Commerce\Product\Upload\ProductCreateDispatcher(
				$c['product.create'], $c['product.detail.edit'], $c['event.dispatcher']
			);
		});

		$services['product.upload.unit_create_dispatcher'] = $services->factory(function ($c) {
			return new Commerce\Product\Upload\UnitCreateDispatcher($c['product.unit.create'], $c['product.unit.edit'], $c['event.dispatcher']);
		});

		$services['product.upload.complete_dispatcher'] = $services->factory(function ($c) {
			return new Commerce\Product\Upload\UploadCompleteDispatcher($c['event.dispatcher']);
		});

		$services['product.upload.unit_stock'] = $services->factory(function($c) {
			return new Commerce\Product\Upload\UnitStockSetter(
				$c['stock.manager'],
				$c['stock.movement.reasons']->get(Reason\Reasons::NEW_ORDER),
				$c['stock.locations'],
				$c['product.upload.heading_keys']
			);
		});

		$services['product.upload.image_create'] = function($c) {
			return new Commerce\Product\Upload\ProductImageCreate(
				$c['product.image.assignor'],
				$c['product.image.create'],
				$c['product.upload.heading_keys']
			);
		};

		$services->extend('field.collection', function($fields, $c) {
			$fields->add(new \Message\Mothership\Commerce\FieldType\Product($c['product.loader'], $c['commerce.field.product_list']));
			$fields->add(new \Message\Mothership\Commerce\FieldType\Productoption($c['product.option.loader']));

			return $fields;
		});

		$services['commerce.field.product_list'] = function($c) {
			return new \Message\Mothership\Commerce\FieldType\Helper\ProductList($c['db.query']);
		};

		/**
		 * @deprecated Left in for BC. Use product.option.loader
		 */
		$services['option.loader'] = $services->factory(function($c) {
			return $c['product.option.loader'];
		});

		/**
		 * Get the tax address
		 */
		$services['product.tax.default_address'] = function($c) {
			if (!$c['user.current'] instanceof \Message\User\AnonymousUser && $addresses = $c['commerce.user.address.loader']->getByUser($c['user.current'])) {
				foreach ($addresses as $address) {
					if($address->type === 'delivery') {
						return $address;
					}
				}
			}

			return $c['product.tax.strategy']->getDefaultStrategyAddress();
		};

		$services['product.tax.company_address'] = function($c) {
			$address = new Commerce\Address\Address;

			$address->countryID = $c['cfg']->tax->taxAddress->country;
			$address->regionID = $c['cfg']->tax->taxAddress->region;

			return $address;
		};

		$services['product.tax.address'] = function($c) {
			// test this to prevent an infinite loop which can occur
			if ($c['http.session']->get('basket.order')) {
				return $c['basket.order']->getAddress('delivery') ?: $c['product.tax.default_address'];
			}

			return $c['product.tax.default_address'];
		};

		$services['product.tax.resolver'] = function($c) {
			return new Commerce\Product\Tax\Resolver\TaxResolver($c['cfg']->tax->rates);
		};

		$services['product.tax.strategy'] = function($c) {
			return $c['cfg']->tax->taxStrategy === 'inclusive' ?
				new Strategy\InclusiveTaxStrategy($c['product.tax.resolver'], $c['product.tax.company_address']) :
				new Strategy\ExclusiveTaxStrategy;
		};

		/**
		 * @deprecated Here to prevent BC breaks, some sites extend this
		 */
		$services['product.tax.rates'] = function($c) {
			return [];
		};

		$services['product.option.loader'] = $services->factory(function($c) {
			return new Commerce\Product\OptionLoader($c['db.query'], $c['locale']);
		});

		$services['product.category.loader'] = $services->factory(function($c) {
			return new Commerce\Product\Category\Loader($c['db.query']);
		});

		$services['product.types'] = function($c) {
			return new Commerce\Product\Type\Collection(array(
				new Commerce\Product\Type\BasicProductType(),
			));
		};

		$services['product.form.attributes'] = $services->factory(function($c) {
			return new Commerce\Product\Form\ProductAttributes($c);
		});


		$services['product.form.search'] = $services->factory(function($c) {
			return new Commerce\Product\Form\ProductSearch($c['translator']);
		});

		$services['product.form.barcode'] = $services->factory(function($c) {
			return new Commerce\Product\Form\Barcode($c['stock.locations']);
		});

		$services['product.form.csv_upload'] = $services->factory(function($c) {
			return new Commerce\Form\Product\CsvUpload;
		});

		$services['product.form.upload_confirm'] = $services->factory(function($c) {
			return new \Message\Mothership\Commerce\Form\Product\CsvUploadConfirm($c['routing.generator']);
		});

		$services['product.form.prices'] = $services->factory(function($c) {
			return new Commerce\Product\Form\ProductPricing($c['product.tax.rates']);
		});

		$services['product.form.unit.edit'] = $services->factory(function($c) {
			return new Commerce\Product\Form\UnitEdit($c['currency.supported'], $c['product.option.loader']);
		});

		$services['product.form.unit.barcode'] = $services->factory(function($c) {
			return new Commerce\Product\Form\UnitBarcode;
		});

		$services['product.form.unit.add'] = $services->factory(function($c) {
			return new Commerce\Product\Form\UnitAdd($c['currency.supported'], $c['product.option.loader']);
		});

		$services['product.detail.loader'] = function($c) {
			return $c['product.entity_loaders']->get('details');
		};

		$services['product.detail.edit'] = function($c) {
			return new Commerce\Product\Type\DetailEdit(
				$c['db.transaction'],
				$c['event.dispatcher'],
				$c['user.current']
			);
		};

		$services['product.barcode.generate'] = function($c) {
			return new Commerce\Product\Barcode\Generate(
				$c['db.query'],
				new Commerce\Product\Barcode\ImageResource,
				$c['product.barcode.sheet']->getBarcodeHeight(),
				$c['product.barcode.sheet']->getBarcodeWidth(),
				$c['cfg']->barcode->fileType,
				$c['product.barcode.code_generator']->getBarcodeType()
			);
		};

		$services['product.barcode.sheet.collection'] = function($c) {
			$collection = new Commerce\Product\Barcode\Sheet\Collection;
			$collection->add(new Commerce\Product\Barcode\Sheet\Size5x13);
			$collection->add(new Commerce\Product\Barcode\Sheet\Size3x8);

			return $collection;
		};

		$services['product.barcode.sheet'] = function($c) {
			return $c['product.barcode.sheet.collection']->get(
				$c['cfg']->barcode->sheetType
			);
		};

		$services['product.barcode.edit'] = function ($c) {
			return new Commerce\Product\Unit\BarcodeEdit($c['db.query'], $c['product.barcode.code_generator']);
		};

		$services['product.barcode.code_generator'] = function ($c) {
			$config = $c['cfg']->barcode;
			$collection = $c['product.barcode.code_generator.collection'];

			if (isset($config->generator)) {
				return $collection->get($config->generator);
			}

			// Check deprecated 'barcode-type' option
			if (isset($config->barcodeType)) {
				return $collection->getByType($config->barcodeType);
			}

			return $collection->getDefault();
		};

		$services['product.barcode.code_generator.collection'] = function ($c) {
			return new Commerce\Product\Barcode\CodeGenerator\GeneratorCollection([
				$c['product.barcode.code_generator.code39'],
				$c['product.barcode.code_generator.ean13'],
			], 'ean13');
		};

		$services['product.barcode.code_generator.code39'] = function ($c) {
			return new Commerce\Product\Barcode\CodeGenerator\Code39Generator;
		};

		$services['product.barcode.code_generator.ean13'] = function ($c) {
			return new Commerce\Product\Barcode\CodeGenerator\Ean13Generator;
		};

		/**
		 * @deprecated User 'user.address.loader' instead
		 */
		$services['commerce.user.address.loader'] = $services->factory(function($c) {
			return $c['user.address.loader'];
		});

		/**
		 * @deprecated Use 'user.address.create' instead
		 */
		$services['commerce.user.address.create'] = $services->factory(function($c) {
			return $c['user.address.create'];
		});

		/**
		 * @deprecated User 'user.address.edit' instead
		 */
		$services['commerce.user.address.edit'] = $services->factory(function($c) {
			return $c['user.address.edit'];
		});

		$services['commerce.order.user_address.loader'] = function($c) {
			return new Commerce\Order\Entity\Address\UserAddressLoader(
				$c['db.query'],
				$c['country.list'],
				$c['state.list']
			);
		};

		$services['user.address.loader'] = $services->extend('user.address.loader', function($loader, $c) {
			return new Commerce\User\Address\Loader(
				$c['db.query'],
				$c['country.list'],
				$c['state.list']
			);
		});

		$services['user.address.types'] = $services->extend('user.address.types', function($types, $c) {
			$types[] = 'billing';
			$types[] = 'delivery';

			return $types;
		});

		$services['user.tabs'] = $services->extend('user.tabs', function ($tabs, $c) {
			$tabs['ms.cp.user.admin.orderhistory'] = 'Order history';

			return $tabs;
		});

		$services['stock.manager'] = $services->factory(function($c) {
			$trans = $c['db.transaction'];
			return new Commerce\Product\Stock\StockManager(
				$trans,
				new Commerce\Product\Stock\Movement\Create(
					$trans,
					$c['user.current'],
					new Commerce\Product\Stock\Movement\Adjustment\Create($trans)
				),
				new Commerce\Product\Stock\Movement\Adjustment\Create($trans),
				$c['product.unit.edit'],
				$c['event.dispatcher']
			);
		});

		$services['stock.locations'] = function() {
			return new Commerce\Product\Stock\Location\Collection;
		};

		$services['stock.default.location'] = function($c) {
			return $c['stock.locations']->get('web');
		};

		$services['stock.movement.loader'] = $services->factory(function($c) {
			return new Commerce\Product\Stock\Movement\Loader(
				$c['db.query'],
				new Commerce\Product\Stock\Movement\Adjustment\Loader(
					$c['db.query'],
					$c['product.unit.loader'],
					$c['stock.locations']
				),
				$c['stock.movement.reasons']
			);
		});

		$services['stock.movement.reasons'] = function() {
			return new Commerce\Product\Stock\Movement\Reason\Collection(array(
				new Reason\Reason(Reason\Reasons::NEW_ORDER, 'New Order'),
				new Reason\Reason(Reason\Reasons::CANCELLED_ORDER, 'Cancelled Order'),
				new Reason\Reason(Reason\Reasons::CANCELLED_ITEM, 'Cancelled Item'),
				new Reason\Reason(Commerce\Task\Stock\Barcode::REASON, 'Stock Take'),

				// add this as constant somewhere
				new Reason\Reason('void_transaction', 'Voided Transaction'),
			));
		};

		$services['stock.movement.iterator'] = $services->factory(function($c) {
			return new Commerce\Product\Stock\Movement\Iterator(
				$c['stock.movement.loader'],
				$c['stock.locations']
			);
		});

		$services['shipping.methods'] = function($c) {
			return new Commerce\Shipping\MethodCollection;
		};

		$services['forex'] = function($c) { // TODO
			return new Commerce\Forex\Forex(
				$c['db.query'],
				'GBP',
				array('GBP', 'USD', 'EUR', 'JPY')
			);
		};

		$services['forex.feed'] = function($c) {
			return new Commerce\Forex\Feed\ECB($c['db.query']);
		};

		/*
		 * Basket
		 */
		$services['order.basket.create'] = $services->factory(function($c) {
			return new Commerce\Order\Basket\Create($c['db.query']);
		});

		$services['order.basket.edit'] = $services->factory(function($c) {
			return new Commerce\Order\Basket\Edit($c['db.query']);
		});

		$services['order.basket.delete'] = $services->factory(function($c) {
			return new Commerce\Order\Basket\Delete($c['db.query']);
		});

		$services['order.basket.loader'] = function($c) {
			return new Commerce\Order\Basket\Loader($c['db.query'], $c['order.basket.token']);
		};

		$services['order.basket.token'] = $services->factory(function($c) {
			return new Commerce\Order\Basket\Token($c['user.password_hash'], $c['cfg']);
		});

		$services['gateway'] = $services->factory(function ($c) {
			throw new \LogicException('`gateway` service does not exist in `cog-mothership-commerce`. Relying on soft dependency of `cog-mothership-ecommerce` to process refund.');
		});
	}

	public function registerEmails($services)
	{
		$services['mail.factory.order.note.notification'] = $services->factory(function($c) {
			$factory = new \Message\Cog\Mail\Factory($c['mail.message']);

			$factory->requires('order', 'note');

			$appName = $c['cfg']->app->name;

			$factory->extend(function($factory, $message) use ($appName) {
				$message->setTo($factory->order->user->email, $factory->order->user->getName());
				$message->setSubject(sprintf('Updates to your %s order - %d', $appName, $factory->order->orderID));
				$message->setView('Message:Mothership:Commerce::mail:order:note:customer-notification', array(
					'order' => $factory->order,
					'note'  => $factory->note,
				));
			});

			return $factory;
		});


		$services['mail.factory.order.cancellation'] = $services->factory(function($c) {
			$factory = new \Message\Cog\Mail\Factory($c['mail.message']);

			$factory->requires('order');

			$appName = $c['cfg']->app->name;

			$factory->extend(function($factory, $message) use ($appName) {
				$message->setTo($factory->order->user->email);
				$message->setSubject(sprintf('Your %s order has been cancelled - %d', $appName, $factory->order->orderID));
				$message->setView('Message:Mothership:Commerce::mail:order:cancel:order-cancellation', array(
					'order'       => $factory->order,
					'companyName' => $appName,
				));
			});

			return $factory;
		});

		$services['mail.factory.order.item.cancellation'] = $services->factory(function($c) {
			$factory = new \Message\Cog\Mail\Factory($c['mail.message']);

			$factory->requires('order');

			$appName = $c['cfg']->app->name;

			$factory->extend(function($factory, $message) use ($appName) {
				$message->setTo($factory->order->user->email);
				$message->setSubject(sprintf('An item of your %s order has been cancelled - %d', $appName, $factory->order->orderID));
				$message->setView('Message:Mothership:Commerce::mail:order:cancel:item-cancellation', array(
					'order'          => $factory->order,
					'cancelledItems' => $factory->order->items->getByCurrentStatusCode(OrderStatuses::CANCELLED),
					'companyName'    => $appName,
				));
			});

			return $factory;
		});
	}

	public function registerProductPageMapper($services)
	{
		// Service to map pages to products and vice-versa
		$services['product.page_mapper.simple'] = function($c) {
			$mapper = new \Message\Mothership\Commerce\ProductPageMapper\SimpleMapper(
				$c['db.query'],
				$c['cms.page.loader'],
				$c['cms.page.authorisation'],
				$c['product.loader'],
				$c['product.unit.loader']
			);

			$mapper->setValidFieldNames('product');
			$mapper->setValidGroupNames(null);
			$mapper->setValidPageTypes('product');

			return $mapper;
		};

		$services['product.page_mapper.option_criteria'] = function($c) {
			$mapper = new \Message\Mothership\Commerce\ProductPageMapper\OptionCriteriaMapper(
				$c['db.query'],
				$c['cms.page.loader'],
				$c['cms.page.authorisation'],
				$c['product.loader'],
				$c['product.unit.loader']
			);

			$mapper->setValidFieldNames('product');
			$mapper->setValidGroupNames(null);
			$mapper->setValidPageTypes('product');

			return $mapper;
		};

		// Set the default product page mapper to the simple mapper
		$services['product.page_mapper'] = $services->raw('product.page_mapper.simple');
		$services['page.product_mapper'] = $services->raw('product.page_mapper.simple');

		// Extend twig with the product/page finders
		$services->extend('templating.twig.environment', function($twig, $c) {
			$twig->addExtension(new \Message\Mothership\Commerce\ProductPageMapper\Templating\TwigExtension(
				$c['page.product_mapper'],
				$c['product.page_mapper']
			));

			return $twig;
		});
	}

	public function registerStatisticsDatasets($services)
	{
		$services->extend('statistics', function($statistics, $c) {
			$statistics->add(new Commerce\Statistic\OrdersIn     ($c['db.query'], $c['statistics.counter'], $c['statistics.range.date']));
			$statistics->add(new Commerce\Statistic\OrdersOut    ($c['db.query'], $c['statistics.counter'], $c['statistics.range.date']));
			$statistics->add(new Commerce\Statistic\SalesNet     ($c['db.query'], $c['statistics.counter'], $c['statistics.range.date']));
			$statistics->add(new Commerce\Statistic\SalesGross   ($c['db.query'], $c['statistics.counter'], $c['statistics.range.date']));
			$statistics->add(new Commerce\Statistic\ProductsSales($c['db.query'], $c['statistics.counter.key'], $c['statistics.range.date']));

			return $statistics;
		});
	}

	public function registerReports($services)
	{
		$services['commerce.stock_summary'] = $services->factory(function($c) {
			return new Commerce\Report\StockSummary(
				$c['db.query.builder.factory'],
				$c['routing.generator'],
				$c['currency']
			);
		});

		$services['commerce.payments_refunds'] = $services->factory(function($c) {
			return new Commerce\Report\PaymentsAndRefunds(
				$c['db.query.builder.factory'],
				$c['routing.generator'],
				$c['event.dispatcher']
			);
		});

		$services['commerce.sales_by_month'] = $services->factory(function($c) {
			return new Commerce\Report\SalesByMonth(
				$c['db.query.builder.factory'],
				$c['routing.generator'],
				$c['event.dispatcher'],
				$c['currency.supported']
			);
		});

		$services['commerce.sales_by_day'] = $services->factory(function($c) {
			return new Commerce\Report\SalesByDay(
				$c['db.query.builder.factory'],
				$c['routing.generator'],
				$c['event.dispatcher'],
				$c['currency.supported']
			);
		});

		$services['commerce.sales_by_order'] = $services->factory(function($c) {
			return new Commerce\Report\SalesByOrder(
				$c['db.query.builder.factory'],
				$c['routing.generator'],
				$c['event.dispatcher'],
				$c['currency.supported']
			);
		});

		$services['commerce.sales_by_item'] = $services->factory(function($c) {
			return new Commerce\Report\SalesByItem(
				$c['db.query.builder.factory'],
				$c['routing.generator'],
				$c['event.dispatcher'],
				$c['currency.supported']
			);
		});

		$services['commerce.sales_by_product'] = $services->factory(function($c) {
			return new Commerce\Report\SalesByProduct(
				$c['db.query.builder.factory'],
				$c['routing.generator'],
				$c['event.dispatcher'],
				$c['currency.supported']
			);
		});

		$services['commerce.sales_by_unit'] = $services->factory(function($c) {
			return new Commerce\Report\SalesByUnit(
				$c['db.query.builder.factory'],
				$c['routing.generator'],
				$c['event.dispatcher'],
				$c['currency.supported']
			);
		});

		$services['commerce.sales_by_location'] = $services->factory(function($c) {
			return new Commerce\Report\SalesByLocation(
				$c['db.query.builder.factory'],
				$c['routing.generator'],
				$c['event.dispatcher'],
				$c['currency.supported']
			);
		});

		$services['commerce.sales_by_user'] = $services->factory(function($c) {
			return new Commerce\Report\SalesByUser(
				$c['db.query.builder.factory'],
				$c['routing.generator'],
				$c['event.dispatcher'],
				$c['currency.supported']
			);
		});

		$services['commerce.reports'] = function($c) {
			$reports = new ReportCollection;
			$reports
				->add($c['commerce.stock_summary'])
				->add($c['commerce.payments_refunds'])
				->add($c['commerce.sales_by_month'])
				->add($c['commerce.sales_by_day'])
				->add($c['commerce.sales_by_order'])
				->add($c['commerce.sales_by_item'])
				->add($c['commerce.sales_by_product'])
				->add($c['commerce.sales_by_unit'])
				->add($c['commerce.sales_by_location'])
				->add($c['commerce.sales_by_user'])
			;
			return $reports;
		};

		$services['commerce.report.sales-data'] = function($c) {
			return new \Message\Mothership\Report\Report\AppendQuery\Collection([
				new Commerce\Report\AppendQuery\Sales($c['db.query.builder.factory']),
				new Commerce\Report\AppendQuery\Shipping($c['db.query.builder.factory']),
			]);
		};

		$services['commerce.report.transaction-data'] = function($c) {
			return new \Message\Mothership\Report\Report\AppendQuery\Collection([
				new Commerce\Report\AppendQuery\Payments($c['db.query.builder.factory']),
			]);
		};

	}

	public function setupCurrencies($services)
	{
		$services['currency'] = function($c) {
			return $c['currency.resolver']->getCurrency();
		};

		$services['currency.supported'] = function($c) {
			if(!(isset($c['cfg']->currency) && isset($c['cfg']->currency->supportedCurrencies))) {
				return [ $c['currency'] ];
			}

			return $c['cfg']->currency->supportedCurrencies;
		};

		$services->extend('templating.twig.environment', function($twgEnv, $c) {
			$twgEnv->getExtension('price_twig_extension')->setDefaultCurrency($c['currency']);

			return $twgEnv;
		});

		$services['currency.form.select'] = $services->factory(function($c) {
			return new Commerce\Form\Currency\CurrencySelect;
		});

		$services['currency.cookie.name'] = function($c) {
			$default = 'ms-commerce-currency';
			$sessionNamespace = $c['cfg']->app->sessionNamespace;

			if(!(isset($c['cfg']->currency) && isset($c['cfg']->currency->cookieName)) || $default === $c['cfg']->currency->cookieName) {
				return $sessionNamespace . '_' . $default;
			}

			return $c['cfg']->currency->cookieName;
		};

		$services['currency.cookie.value'] = function($c) {
			if (!isset($c['request'])) {
				// not a request so no cookie will be set.
				return null;
			}

			return $c['request']->cookies->get($c['currency.cookie.name']);
		};

		$services['currency.default'] = $services->factory(function($c) {
			return isset($c['cfg']->currency->defaultCurrency)?$c['cfg']->currency->defaultCurrency:'GBP';
		});

		$services['currency.company'] = $services->factory(function($c) {
			return isset($c['cfg']->currency->defaultCurrency)?$c['cfg']->currency->companyCurrency:$c['currency.default'];
		});

		$services['currency.resolver'] = $services->factory(function($c) {
			return new Commerce\Currency\CurrencyResolver($c['currency.default'], $c['currency.cookie.value']);
		});
	}
}
