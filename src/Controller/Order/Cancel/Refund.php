<?php

namespace Message\Mothership\Commerce\Controller\Order\Cancel;

use Message\Mothership\Commerce\Order\CancellationRefund;
use Message\Mothership\Commerce\Order\Entity\Payment\Payment as OrderPayment;
use Message\Mothership\Commerce\Order\Entity\Refund\Refund as OrderRefund;
use Message\Mothership\Commerce\Order\Entity\Payment\MethodInterface;
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
	const ITEM_URL = 'ms.commerce.order.detail.view.item';

	/**
	 * Success method for cancelled orders.
	 */
	public function orderSuccess(PayableInterface $payable, $reference, MethodInterface $method)
	{
		return $this->success($payable, $reference, $method, self::ORDER_URL);
	}

	/**
	 * Failure method for cancelled orders.
	 */
	public function orderFailure(PayableInterface $payable)
	{
		return $this->failure($payable, self::ORDER_URL);
	}

	/**
	 * Success method for cancelled items.
	 */
	public function itemSuccess(PayableInterface $payable, $reference, MethodInterface $method)
	{
		return $this->success($payable, $reference, $method, self::ITEM_URL);
	}

	/**
	 * Failure method for cancelled items.
	 */
	public function itemFailure(PayableInterface $payable)
	{
		return $this->failure($payable, self::ITEM_URL);
	}

	/**
	 * Creates payment and refund for $payable.
	 * {@inheritdoc}
	 */
	public function success(
		PayableInterface $payable,
		$reference,
		MethodInterface $method,
		$url
	) {
		de($this->get('request'));

		$payment            = new OrderPayment;
		$payment->method    = $method;
		$payment->amount    = $payable->getPayableAmount();
		$payment->reference = $reference;

		$refund            = new OrderRefund;
		$refund->payment   = $payment;
		$refund->method    = $payment->method;
		$refund->amount    = $payment->amount;
		$refund->reference = $payment->reference;

		$order = $payable->getOrder();
		$order->payments->append($payment);
		$order->refunds->append($refund);

		$transaction = $this->get('db.transaction');
		$this->get('order.payment.create')
			->setTransaction($transaction)
			->create($payment);

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

		$this->_returnResponse($url);
	}

	public function failure(PayableInterface $payable, $url)
	{
		$this->addFlash(
			'error',
			sprintf(
				'Could not refund %s %s to customer. Please refund the amount manually.',
				number_format($payable->getPayableAmount(), 2),
				$payable->getPayableCurrency()
			)
		);

		$this->_returnResponse($url);
	}

	protected function _returnResponse($url)
	{
		$successUrl = $this->generateUrl($url, array(
			'orderID' => $payable->getOrder()->id,
		), UrlGeneratorInterface::ABSOLUTE_URL);

		$response = new JsonResponse;
		$response->setData([
			'url' => $successUrl,
		]);

		return $response;
	}
}