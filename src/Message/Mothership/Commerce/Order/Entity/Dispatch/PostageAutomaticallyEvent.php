<?php

namespace Message\Mothership\Commerce\Order\Entity\Dispatch;

use Message\Mothership\Commerce\Order\Entity\Document\Document;

use Message\Cog\Event\Event;

class PostageAutomaticallyEvent extends Event
{
	protected $_dispatch;

	protected $_code;
	protected $_cost;
	protected $_documents = array();

	public function __construct(Dispatch $dispatch)
	{
		$this->_dispatch = $dispatch;
	}

	public function getDispatch()
	{
		return $this->_dispatch;
	}

	public function getCode()
	{
		return $this->_code;
	}

	public function getCost()
	{
		return $this->_cost;
	}

	public function getDocuments()
	{
		return $this->_documents;
	}

	public function setCode($code)
	{
		if (!$code) {
			return false;
		}

		$this->_code = $code;

		$this->stopPropagation();
	}

	public function setCost($cost)
	{
		$this->_cost = (float) $cost;
	}

	public function addDocument(Document $document)
	{
		$this->_documents[] = $document;
	}
}