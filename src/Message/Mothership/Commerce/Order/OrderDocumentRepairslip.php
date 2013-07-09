<?php


class OrderDocumentRepairslip extends OrderDocument {


	public function getDocument() {

		$delivery = $this->order->getAddress('delivery');
		$invoice  = $this->order->getAddress('invoice');

		$repair = $this->order->getRepairs();
		// At the moment, there's only ever one.
		$repair = $repair[0];

		$purchaseAddress = ($repair->retailer == -1) ? array('Gift') : $invoice->label();

		$isRetailer = ($repair->retailer == $this->order->userID);

		$user = getUserDetails($this->order->userID);

		$retailerAddress = 'uniformwares.com';

		if($repair->retailer) {
			$retailerAddress = '';
		}

		return '
			<div class="page">
				<img src="/images/repair_slip_logo.png" alt="" />
				<div class="packingslip repairslip">
					<h1>'.($isRetailer ? 'Retailer' : 'Customer').' - Return For Repair</h1>
					<section class="customer">
						<header>
							<h2 class="four col">'.($isRetailer ? 'Retailer' : 'Customer').' details</h2>
							<h2 class="one col">RFR Number</h2>
							<h2 class="one col">RFR Date</h2>
						</header>

						<dl class="two col">
							<dt>Name</dt>
								<dd>' . $user['user_name'] . '</dd>

							<dt>Billing address</dt>
								<dd>' . implode('<br>', $invoice->label(array('name'))) . '</dd>

							<dt>Phone</dt>
								<dd>' . $invoice->telephone . '</dd>

							<dt>Email</dt>
								<dd>' . $user['email_name'] . '</dd>
						</dl>

						<strong class="one col">' . $this->order->orderID . '</strong>
						<strong class="one col">' . date('d/m/Y', $this->order->placedTimestamp) . '</strong>
					</section>

					<section class="timepiece">
						<header>
							<h2 class="four col">Timepiece Details</h2>
							<h2 class="two col">Description Of Repair Requested</h2>
						</header>

						<dl class="two col">
							<dt>Model</dt>
								<dd>' . $repair->productName . '</dd>

							<dt>Place Of Purchase</dt>
								<dd>' . implode('<br>', $purchaseAddress) . '</dd>

							<dt>Date Of Purchase</dt>
								<dd>'.date('d/m/y', $repair->purchaseDate).'</dd>

							<dt>Warranty Status</dt>
								<dd>'.( $repair->purchaseDate >= ($this->order->placedTimestamp - Repair::WARRANTY_LENGTH) ? 'In Warranty' : 'Out Of Warranty').'</dd>
						</dl>

						<strong class="two col">'.nl2br($repair->notes).'</strong>
					</section>

					<section class="breakdown">
						<header>
							<h2 class="three col">Breakdown Of Repair Requested</h2>
							<h2 class="one col">Unit Cost</h2>
							<h2 class="one col">Quantity</h2>
							<h2 class="one col">Total</h2>
						</header>

						<table>
							' . $this->_getRepairItems($repair) . '
						</table>

					</section>

					<section class="customer">
						<header>
							<h2 class="six col">Return Delivery Details</h2>
						</header>

						<dl class="six col">
							<dt>Name</dt>
								<dd>' . $delivery->name. '</dd>

							<dt>Delivery address</dt>
								<dd>' . implode('<br>', $delivery->label(array('name'))) . '</dd>

							<dt>Phone</dt>
								<dd>' . $delivery->telephone . '</dd>
						</dl>
					</section>

				</div>
			</div>
			<div class="page">
				<img src="/images/rfr-return-label.jpg" width="780" height="1113" />
			</div>
		';
	}

	protected function _getRepairItems($repair) {
		$html = '';

		foreach($repair->getOptions() as $option) {
			$html.= '
				<tr>
					<td class="three col">' . $option->name . '</td>
					<td class="col"><b>£' . number_format($option->price, 2) . '</b></td>
					<td class="col"><b>x 1</b></td>
					<td class="col"><b>£' . number_format($option->price, 2) . '</b></td>
				</tr>
			';
		}

		// shipping
		$html.= '
				<tr>
					<td class="three col">' . $this->order->shippingName . '</td>
					<td class="col"><b>£' . number_format($this->order->shippingAmount, 2) . '</b></td>
					<td class="col"><b>x 1</b></td>
					<td class="col"><b>£' . number_format($this->order->shippingAmount, 2) . '</b></td>
				</tr>
			';

		// totals

		$html.= '
				<tr>
					<td class="four col"></td>
					<td class="col"><b>VAT (Included)</b></td>
					<td class="col"><b>£' . number_format($this->order->tax, 2) . '</b></td>
				</tr>
			';

		$html.= '
				<tr>
					<td class="four col"></td>
					<td class="col"><b>Grand Total</b></td>
					<td class="col"><b>£' . number_format($this->order->getTotal(), 2) . '</b></td>
				</tr>
			';

		return $html;

	}


}






?>