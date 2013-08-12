<?php

namespace Message\Mothership\Commerce\Order\Entity\Note;

use Message\Mothership\Commerce\Order\Entity\EntityInterface;

use Message\Cog\ValueObject\Authorship;

class Note implements EntityInterface
{
	const TYPE_CHECKOUT   = 'checkout';
	const TYPE_RETURN     = 'return';
	const TYPE_ORDER_VIEW = 'order_view';

	public $id;

	public $order;
	public $authorship;

	public $note;
	public $customerNotified;
	public $raisedFrom;

	public function __construct()
	{
		$this->authorship = new Authorship;

		$this->authorship
			->disableUpdate()
			->disableDelete();
	}

	public function sendCustomerNotification(Order $order)
	{

		if(!$this->notifyCustomer) {
			return false;
		}

		$subject = 'Updates to your ' . Config::get('merchant')->name . ' Order ' . $order->orderID;
		$headers = implode("\r\n", array(
			'Content-type: text/plain; charset=UTF-8;',
			'From: do-not-reply@' . Config::get('merchant')->domain,
		));
		$mailintro  = 'An admininstrator has added a note to your order:'."\n\n";
		$mailbody   = $this->note."\n\n";
		$mailfooter =  Config::get('merchant')->name."\n";
		$mailfooter.= "Telephone ".Config::get('merchant')->telephone."\nE-mail:".Config::get('merchant')->email."\n\n";
		$mailfooter.= ". . . . . . . . . . . . . . . . . . . . . .\n\n";
		$mailfooter.= ($order->taxable) ? "A VAT receipt is available here: http://".Config::get('merchant')->url."/account/order.php?order_id=".$order->orderID."\n" : '';
		$mailfooter.= "Please do not reply to this email. For any inquiries regarding orders please email ".Config::get('merchant')->email." Thank you.\n\n";
		$mailfooter.= "In respect of errors, omissions or orders identified for re-sale purposes, ".Config::get('merchant')->name." reserve the right to cancel or amend these  orders accordingly.";

		$customer = getUserDetails($order->userID);

		return mail($customer['email_name'], $subject, ($mailintro.$mailbody.$mailfooter), $headers);
	}
}