<?php

/*

OrderUpdate is used for processing orders
Its methods allow an order to receive despatch, refund and exchange items


*/

class OrderUpdate extends OrderAppend {

	//FLAG FOR WHETHER OR NOT TO COMMIT ON EXIT
	protected $autoCommit = false;

	//ADD ITEMS TO THIS ORDER: EXCHANGES
	public function addBasket(Basket $basket) {
		parent::addBasket($basket);
		$this->items->commit();
		$this->update();
	}

	//ADD ITEMS TO THIS ORDER: EXCHANGES
	public function addItemAndSave(Unit $unit) {
		$this->addItem($unit);
		$this->items->commit();
		$this->update();
	}


	//ADD ITEMS TO THIS ORDER: EXCHANGES
	public function addTillTransaction(TillTransaction $basket) {
		parent::addBasket($basket);
		$this->items->commit();
		$this->update();
	}


	//ADD A RECEIPT TO THIS ORDER
	public function addReceipt(OrderReceipt $receipt) {
		$num = count($this->receipts->getItems());
		$this->receipts->add($receipt);
		$this->receipts->commit();
		$this->updateOrderSummary();
		//RETURN THE NEW RECEIPT
		if (count($this->receipts->getItems()) > $num) {
			return current($this->receipts->getItems());
		}
		return false;
	}


	//ADD A DESPATCH ITEM TO THE ORDER
	public function addDespatch(OrderDespatch $despatch) {
		$num = count($this->despatches->getItems());
		$this->despatches->add($despatch);
		$this->despatches->commit();
		//RELOAD THE ORDER ITEMS TO REFRESH STATUS
		$this->items->reload();
		$this->update();
		//RETURN THE NEW DESPATCH
		if (count($this->despatches->getItems()) > $num) {
			return current($this->despatches->getItems());
		}
		return false;
	}


	//UPDATE A DESPATCH PACKAGE
	public function postageDespatch($despatchID, $code, $cost, $staffID) {
		if ($despatch = $this->getDespatches($despatchID)) {
			$despatch->code($code);
			$despatch->cost($cost);
			$despatch->staffID($staffID);
			if ($despatch->update()) {
				$this->update();
				return true;
			}
		}
		return false;
	}


	//SHIP A PACKAGE
	public function shipDespatch($despatchID, $staffID) {
		if ($despatch = $this->getDespatches($despatchID)) {
			$despatch->staffID($staffID);
			$despatch->despatchTimestamp('NOW()');
			if ($despatch->update()) {

				//SEND DESPATCH CONFIRMATION EMAIL
				$userDetails = getUserDetails($this->userID);

				if($userDetails['email_name']
				&& !$despatch instanceof OrderDespatchElectronic
				&& !$despatch instanceof OrderDespatchCollect) {

					$delivery = $this->getAddress('delivery');

					$emailBody  = "Your  order has now been despatched. Delivery and order details are as follows.\n\n";
					$emailBody .= "------------------------------------------------------------------\n\n";
					$emailBody .= "Order Details \n\n";

					foreach($this->getItems() as $item){
						if(in_array($item->itemID, $despatch->getItemIDs())) {
							$emailBody .=  "- ".$item->brandName.', '.$item->description."\n";
						}
					}
					$emailBody .= "\n";
					if(!$despatch instanceof OrderDespatchMetapack) {
						$emailBody .= "Delivery method: ".$despatch->typeName."\n\n";
					}
					$emailBody .= "Delivery address:\n";
					$emailBody .= $delivery->name."\n";
					$emailBody .= trim($delivery->address_1)."\n";
					if(!empty($delivery->address_2) || $delivery->address_2 != ""){
						$emailBody .= trim($delivery->address_2)."\n";
					}
					$emailBody .= $delivery->town."\n";
					$emailBody .= $delivery->postcode."\n";
					$emailBody .= $delivery->country."\n\n";
					$emailBody .= "Track your order:\n";
					if($despatch->getTrackingLink()) {
						$emailBody .= "Your ".$despatch->typeName." order tracking number is ".$despatch->code." please click the following link to check on the delivery status of your order.\n";
						$emailBody .= $despatch->getTrackingLink()->href."\n\n";
					}
					elseif($despatch instanceof OrderDespatchMetapack) {
						$emailBody .= "You can track your order by logging in to your  account and selecting the order in 'View order history' within the 'Your account' section\n\n";
					}
					$emailBody .= "All orders must be signed for when you receive your delivery.\n\n";
					$msg = new ArchivedEmailMessage;
					$msg->orderID = $this->orderID;
					$msg->userID = $this->userID;
					$msg->emailID = $userDetails['email_id'];
					$msg->emailSender = Config::get('merchant')->email;
					$msg->emailSubject = 'Your '.Config::get('merchant')->name.' Despatch Confirmation - Order '.$this->orderID;
					$msg->emailBody = $emailBody;
					$msg->save();

					$to = "$msg->userName <$msg->recipientEmail>";
					//$to = "Joe Holdcroft <joe@message.uk.com>";//TMP

					if(@mail($to, $msg->emailSubject, $msg->emailBody, 'Content-type: text/plain; charset=UTF-8;
From: ' . $msg->emailSender)){
						$msg->dateSent = date('Y-m-d H:i:s');
					}

					$msg->save();

				}

				$this->update();
				return true;

			}
		}
		return false;
	}


	//DETERMINE THE CORRECT DESPATCH TYPE FOR THIS ORDER
	public function getNewDespatch()
	{
		switch ($this->getCountryID('delivery')) {
			case 'GB':
				$type = 'OrderDespatchFedexUk';
				break;
			default:
				$type = 'OrderDespatchFedex';
		}

		return new $type;
	}


	//ALIAS FOR ADD DESPATCH TO MAKE CODE MORE LOGICAL WHEN WORKING WITH PACKAGES
	public function addPackage(OrderDespatch $package) {
		$this->addDespatch($package);
	}


	public function addRefund(OrderRefund $refund) {
		$this->refunds->add($refund);
		$this->refunds->commit();
		$this->update();
		$refund = current($this->refunds->getitems());
		return $refund->refundID;
	}


	// AG - ADD A PAYMENT TO THIS ORDER AND UPDATE ORDER IN THE DB
	public function addPayment(OrderPayment $payment, $commit=true) {
		$this->payments->add($payment);

		$sql =  $this->payments->getInsertQuery($this->orderID);
		if ($commit && $sql) {
			//START A TRANSACTION
			$trans = new DBtransaction;
			$trans->add($sql);
			//RUN THE TRANSACTION
			if ($trans->run()) {
				$this->load();
			} else {
				throw new OrderException('error saving ' . get_class($this));
			}
		}

		$this->updatePaid();
		$this->updateOrderSummary();
		$payment = current($this->payments->getitems());
		return $payment->paymentID;
	}


	//ADD A RETURN
	public function addReturn(OrderReturn $return) {
		$this->returns->add($return);
		$this->returns->commit();
		$this->update();
		//GET THE RETURN AND SEND IT
		$return = current($this->returns->getItems());
		return $return->returnID;
	}

	//REMOVE A RETURN
	public function deleteReturn($returnID) {
		$this->returns->load();
		$this->returns->delete($returnID);
		$this->update();
	}


	//ONCE A RETURN HAS BEEN COMPLETED, TELL DIMENSIONS AND RELEASE ANY EXCHANGE PRODUCTS INTO SOP
	public function completeReturn($returnID) {
		if ($return = $this->getReturns($returnID)) {
			//TELL DIMENSIONS ABOUT THE CREDIT / REFUND / EXCHANGE (IF NOT DONE SO ALREADY) IF NOT REPAIR
			if(!$return->isSentToDimensions() && $return->resolutionID != 4) {
				$dim = new DimensionsCredit($this, $return);
				$dim->sendCredit();
				if ($return->exchangeItemID) {
					$dim->sendExchange();
				}
				unset($dim);
			}
			//CHANGE EXCHANGE ITEM FROM ON HOLD TO ORDERED
			if ($return->exchangeItemID && $item = $this->getItems($return->exchangeItemID)) {
				$item->releaseHold();
				$this->update();
			}
		}
	}


	public function markPrinted($staffID) {
		$printed = false;
		foreach ($this->getitems() as $item) {
			if ($item->statusID < ORDER_STATUS_PRINTED) {
				$item->updateStatus(ORDER_STATUS_PRINTED, $staffID);
				$printed = true;
			}
		}
		if ($this->statusID == ORDER_STATUS_ORDERED) {
			$this->statusID = ORDER_STATUS_PRINTED;
			$this->updateOrderSummary();
		}
		return $printed;
	}


	public function markReceived($staffID) {
		$received = false;
		foreach ($this->getItems() as $item) {
			if ($item->statusID < ORDER_STATUS_RECEIVED) {
				$item->updateStatus(ORDER_STATUS_RECEIVED, $staffID);
				$received = true;
			}
		}
		if ($this->statusID == ORDER_STATUS_ORDERED || $this->statusID == ORDER_STATUS_PENDING) {
			$this->statusID = ORDER_STATUS_RECEIVED;
			$this->updateOrderSummary();
		}
		return $received;
	}


	public function markStandaloneReturn($staffID) {
		$this->statusID = ORDER_STATUS_STANDALONE_RETURN;
		$this->updateOrderSummary();
	}


	public function markPending($staffID) {
		$pending = false;
		foreach ($this->getItems() as $item) {
			if ($item->statusID == ORDER_STATUS_ORDERED) {
				$item->updateStatus(ORDER_STATUS_PENDING, $staffID);
				$pending = true;
			}
		}
		if ($this->statusID == ORDER_STATUS_ORDERED) {
			$this->statusID = ORDER_STATUS_PENDING;
			$this->updateOrderSummary();
		}
		return $pending;
	}


	public function update() {
		$this->statusID = ($this->statusID == ORDER_STATUS_STANDALONE_RETURN) ? $this->statusID : NULL;
		$partShipped = false;
		$this->items->reload();
		foreach ($this->getitems() as $item) {
			if (is_null($this->statusID)) {
				$this->statusID = $item->statusID;
			} elseif ($item->statusID < $this->statusID) {
				if($this->statusID != ORDER_STATUS_STANDALONE_RETURN) {
					$this->statusID = $item->statusID;
				}
			}
			if ($item->statusID >= ORDER_STATUS_SHIPPED) {
				$partShipped = true;
			}
			if ($this->statusID < ORDER_STATUS_SHIPPED && $partShipped) {
				$this->statusID = ORDER_STATUS_PART_SHIPPED;
			}
		}
		$this->updateOrderSummary();
		$this->load();
	}


	public function updateAddress($type, $data) {
		$address = $this->getAddress($type);
		$res = $address->update($data);
		$this->addresses->reload();
		return $res;
	}


	public function saveNotes() {
		$this->notes->commit();
		$this->notes->load();
	}

	public function updateOrderSummary() {
		$DB = new DBquery;
		$query = 'UPDATE order_summary SET '
			   . 'status_id = ' . $this->statusID . ', '
			   . 'order_payment = ' . $DB->escape($this->paid) . ', '
			   . 'order_change = ' . $DB->escape($this->change) . ', '
			   . 'order_updated = NOW() '
			   . 'WHERE order_id = ' . $this->orderID;
		if (!$DB->query($query)) {
			throw new OrderException('Unable to update order '.$DB->error().' '.$query);
		}
	}

	public function setStatusID($id) {
		$this->statusID = $id;
	}


}




?>