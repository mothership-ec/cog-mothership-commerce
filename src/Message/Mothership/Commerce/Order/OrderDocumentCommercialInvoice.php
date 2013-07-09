<?php


class OrderDocumentCommercialinvoice extends OrderDocument {
	
	
	public function getDocument() {
		
		$despatchID = array_shift($this->params);
		if ($despatch = $this->order->getDespatches($despatchID)) {
			return $despatch->invoice;
		}
		
	}


}






?>