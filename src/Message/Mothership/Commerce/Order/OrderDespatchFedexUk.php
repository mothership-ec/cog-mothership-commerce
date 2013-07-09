<?php

class OrderDespatchFedexUk extends OrderDespatchFedex
{

	protected function setType()
	{
		$this->typeID = 13;
	}

	public function getTrackingLink() {
		if ($this->code) {
			$link->text = 'Track this package (ref:' . $this->code . ') at Fedex.com';
			$link->href = 'http://www.fedexuk.net/accounts/QuickTrack.aspx?consignment=' . $this->code;
			$link->ref  = $this->code;
			return $link;
		}
	}

}