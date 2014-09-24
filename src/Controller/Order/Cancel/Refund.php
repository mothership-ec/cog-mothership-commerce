<?php

namespace Message\Mothership\Commerce\Controller\Order\Cancel;

use Message\Mothership\Commerce\Order\CancellationRefund;
use Message\Mothership\Commerce\Order\Entity\Payment\Payment as OrderPayment;
use Message\Mothership\Commerce\Order\Entity\Refund\Refund as OrderRefund;
use Message\Mothership\Commerce\Payment\MethodInterface;
use Message\Mothership\Ecommerce\Controller\Gateway\CompleteControllerInterface;
use Message\Mothership\Commerce\Payable\PayableInterface;

use Message\Cog\Controller\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Controller for completing a refund when cancelling an item or order.
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class Refund extends Controller implements CompleteControllerInterface
{
	const ORDER_URL = 'ms.commerce.order.detail.view';
	const ITEM_URL = 'ms.commerce.order.detail.view.items';

	const ORDER_REASON = 'Cancelled Order';
	const ITEM_REASON = 'Cancelled Item';

	protected $_url;
	protected $_reason;

	/**
	 * Success method for cancelled orders.
	 */
	public function orderSuccess(PayableInterface $payable, $reference, MethodInterface $method)
	{
		$this->_url    = self::ORDER_URL;
		$this->_reason = self::ORDER_REASON;

		return $this->success($payable, $reference, $method);
	}

	/**
	 * Failure method for cancelled orders.
	 */
	public function orderFailure(PayableInterface $payable)
	{
		$this->_url = self::ORDER_URL;

		return $this->failure($payable);
	}

	/**
	 * Success method for cancelled items.
	 */
	public function itemSuccess(PayableInterface $payable, $reference, MethodInterface $method)
	{
		$this->_url    = self::ITEM_URL;
		$this->_reason = self::ITEM_REASON;

		return $this->success($payable, $reference, $method);
	}

	/**
	 * Failure method for cancelled items.
	 */
	public function itemFailure(PayableInterface $payable)
	{
		$this->_url = self::ITEM_URL;

		return $this->failure($payable);
	}

	/**
	 * Creates payment and refund for $payable.
	 * {@inheritdoc}
	 */
	public function success(
		PayableInterface $payable,
		$reference,
		MethodInterface $method
	) {
		$refund            = new OrderRefund;
		$refund->method    = $method;
		$refund->amount    = $payable->getPayableAmount();
		$refund->reference = $reference;
		$refund->reason    = $this->_reason;
		$refund->payment   = ($this->get('order.payment.loader')->getByMethodAndReference($method, $reference) ?: null);

		$order = $payable->getOrder();
		$order->refunds->append($refund);

		$transaction = $this->get('db.transaction');

		$this->get('order.refund.create')
			->setTransaction($transaction)
			->create($refund);

		if ($transaction->commit()) {
			$this->addFlash(
				'success',
				sprintf(
					'Successfully refunded %s %s to customer.',
					number_format($payable->getPayableAmount(), 2),
					$payable->getPayableCurrency()
				)
			);
		}

		$successUrl = $this->generateUrl($this->_url, array(
			'orderID' => $payable->getOrder()->id,
		), UrlGeneratorInterface::ABSOLUTE_URL);

		return new JsonResponse([
			'url' => $this->get('request')->headers->get('referer'),
		]);
	}

	public function cancel(PayableInterface $payable)
	{
		return $this->failure($payable);
	}

	public function failure(PayableInterface $payable)
	{
		$this->addFlash(
			'error',
			sprintf(
				'Could not refund %s %s to customer. Please refund the amount manually.',
				number_format($payable->getPayableAmount(), 2),
				$payable->getPayableCurrency()
			)
		);

		return $this->redirectToRoute($this->_url, [
			'orderID' => $payable->getOrder()->id
		]);
	}
}