<?php

namespace Message\Mothership\Commerce\Product\Upload;

use Message\Mothership\Commerce\Product\Events;
use Message\Cog\Event\Dispatcher;

class UploadCompleteDispatcher
{
	/**
	 * @var Dispatcher
	 */
	private $_dispatcher;

	public function __construct(Dispatcher $dispatcher)
	{
		$this->_dispatcher = $dispatcher;
	}

	public function dispatch()
	{
		return $this->_dispatcher->dispatch(
			Events::PRODUCT_UPLOAD_COMPLETE,
			new UploadCompleteEvent
		);
	}
}