<?php

namespace Message\Mothership\Commerce\Product\Upload;

use Message\Mothership\Commerce\Product;
use Message\Cog\Event\Dispatcher;

class UnitCreateDispatcher
{
	private $_unitCreate;
	private $_unitEdit;
	private $_dispatcher;

	public function __construct(Product\Unit\Create $unitCreate, Product\Unit\Edit $unitEdit, Dispatcher $dispatcher)
	{
		$this->_unitCreate = $unitCreate;
		$this->_unitEdit   = $unitEdit;
		$this->_dispatcher = $dispatcher;
	}

	public function create(Product\Unit\Unit $unit, array $formData, array $row)
	{
		$unit = $this->_unitCreate->create($unit);
		$this->_unitEdit->saveStock($unit);

		return $this->_dispatchEvent($unit, $formData, $row);
	}

	private function _dispatchEvent(Product\Unit\Unit $unit, array $formData, array $row)
	{
		$event = new UnitCreateEvent($unit, $formData, $row);

		return $this->_dispatcher->dispatch(
			Product\Events::UNIT_UPLOAD_CREATE,
			$event
		)->getUnit();
	}
}