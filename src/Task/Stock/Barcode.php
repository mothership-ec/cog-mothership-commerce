<?php

namespace Message\Mothership\Commerce\Task\Stock;

use Message\Cog\Console\Task\Task;
use Symfony\Component\Console\Input\InputOption;

class Barcode extends Task
{
	protected $_barcodeStock;

	public function configure()
	{
		$this->addArgument(
			'file',
			InputOption::VALUE_REQUIRED,
			'Path to barcode text file'
		);
	}

	public function process()
	{
		$this
			->_setFileName()
			->_parseFile()
			->_setTransaction()
			->_saveStockLevels()
		;
	}

	protected function _setFileName()
	{
		de($this->getRawInput()->getArgument('file'));

		return $this;
	}

	protected function _parseFile()
	{
		return $this;
	}

	protected function _setTransaction()
	{
		return $this;
	}

	protected function _saveStockLevels()
	{
		return $this;
	}
}