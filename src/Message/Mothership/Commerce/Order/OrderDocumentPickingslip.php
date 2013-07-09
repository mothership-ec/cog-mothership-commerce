<?php


class OrderDocumentPickingslip extends OrderDocument {
	
	
	public function getDocument() {
		
		$delivery = $this->order->getAddress('delivery');
		$invoice  = $this->order->getAddress('invoice');

		$user = getUserDetails($this->order->userID);

		$items = $this->groupItems();
		
		switch ($delivery->countryID) {
		
			default:
				$returnAddress = '
					<span>RETURNS Department:</span>
					<span>'.Config::get('merchant')->name.'</span>
					<span>'.Config::get('merchant')->address->line1.'</span>
					<span>'.Config::get('merchant')->address->line2.'</span>					
					<span>'.Config::get('merchant')->address->town.'</span>
					<span>'.Config::get('merchant')->address->postcode.'</span>
					<span>'.Config::get('merchant')->address->country.'</span>
				';
				break;
		}
		
	$notes = array();
	foreach($this->order->getNotes(OrderNote::TYPE_CHECKOUT) as $note) {
		$notes[] = $note->note;
	}

	return '
	<div class="packingslip">
		<h1>Delivery Note</h1>
		
		<section>
			<header>
				<h2 class="four col">Customer details</h2>
				<h2 class="two col">Order Number</h2>
			</header>
			
			<dl class="two col">
				<dt>Billing address</dt>
					<dd>' . implode('<br>', $invoice->label()) . '</dd>
					
				<dt>Phone</dt>
					<dd>' . $delivery->telephone . '</dd>
					
				<dt>Email</dt>
					<dd>' . $user['email_name'] . '</dd>
			</dl>
			
			<div class="two col section">
				<div><strong>' . $this->order->orderID . '</strong></div>
				'.( !$notes ? '' : '
				<div class="notes">
					<span>Notes</span>
					<p>'.nl2br(htmlspecialchars(implode("\n", $notes))).'</p>
				</div>'
				).'
				
			</div>
			

		</section>
		
		<section>
			<header>
				<h2 class="four col">Delivery Details</h2>
				<h2 class="two col">Order Date</h2>
			</header>
			
			<dl class="two col">
				<dt>Delivery address</dt>
					<dd>' . implode('<br>', $delivery->label()) . '</dd>
			</dl>
			
			<strong class="two col">
				<time>' . date('d/m/Y', $this->order->placedTimestamp) . '</time>
			</strong>
		</section>
		
		<section class="items">
			<header>
				<h2 class="four col">Item(s)</h2>
				<h2 class="col">Status</h2>
				<h2 class="col">Quantity</h2>
			</header>
			
			<table>
				' . $this->getItemListingUngrouped() . '
			</table>
		</section>
		
		' . (count($items['remaining']) == 0 ? '<h3><b>Thank you.</b> This order is now complete.</h3>' : '') . '
	</div>
		';
	}


}






?>