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
	/**
	 * Creates payment and refund for $payable.
	 * {@inheritdoc}
	 */
	public function success(PayableInterface $payable, $reference, MethodInterface $method)
	{
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

		$successUrl = $this->generateUrl('ms.commerce.return.view', array(
			'returnID' => $payable->id,
		), UrlGeneratorInterface::ABSOLUTE_URL);

		// Create json response with the success url
		$response = new JsonResponse;
		$response->setData([
			'url' => $successUrl,
		]);

		return $response;
	}

	public function cancel(PayableInterface $payable)
	{
		return $this->failure($payable);
	}

	public function failure(PayableInterface $payable)
	{
		$this->addFlash('error', 'Could not refund ');
	}
}