<?php


class OrderDocumentPackingslip extends OrderDocumentPickingslip {
	
	
	protected function groupItems() {
		$order = array(
			'included'  => array(),
			'sent'      => array(),
			'remaining' => array()
		);
		
		if (isset($this->params['despatchID'])) {
			$despatch = $this->order->getDespatches($this->params['despatchID']);
			foreach ($despatch->getItemIDs() as $id) {
				$items[$id] = true;
			}
		}
		
		foreach ($this->order->getItems() as $item) {
			switch ($item->statusID) {
				
				case $item->statusID >= ORDER_STATUS_SHIPPED:
					$array = 'sent';
					break;
				
				case isset($items[$item->itemID]):
					$array = 'included';
					break;
				
				default:
					$array = 'remaining';
				
			}
			$order[$array][$item->unitID][] = $item;
		}
		return $order;
	}
	

}






?>