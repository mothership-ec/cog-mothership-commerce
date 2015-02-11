<?php

namespace Message\Mothership\Commerce\Controller\Order;

use Message\Cog\Controller\Controller;

class Document extends Controller
{

	public function printDocument($documentID)
	{
		$document = $this->get('order.document.loader')->getByID($documentID);

		return $this->redirectToRoute('ms.cp.file_manager.print', array(
			'path' => $document->file->getRealPath()
		));
	}

}