<?php

namespace Message\Mothership\Commerce\Task\Stock;

use Message\Cog\Console\Task\Task;
use Message\Mothership\Commerce\Product\Unit\Unit;
use Message\Mothership\Commerce\Product\Stock\Movement\Reason\Reason;
use Symfony\Component\Console\Input\InputArgument;

class Barcode extends Task
{
	protected $_filepath;

	/**
	 * @var \Message\Mothership\Commerce\Product\Stock\StockManager
	 */
	protected $_stockManager;

	/**
	 * @var \Message\Mothership\Commerce\Product\Stock\Location\Location
	 */
	protected $_location;

	/**
	 * @var \Message\Mothership\Commerce\Product\Unit\Loader
	 */
	protected $_unitLoader;

	protected $_barcodeStock = [];

	protected $_clearStock = false;

	public function configure()
	{
		$this->addArgument(
			'file',
			InputArgument::REQUIRED,
			'Path to barcode text file'
		);

		$this->addArgument(
			'clearStock',
			InputArgument::OPTIONAL,
			'Reset all stock levels before running script (set as 1)',
			0
		);

		$this->addArgument(
			'location',
			InputArgument::OPTIONAL,
			'Reset all stock levels before running script (set as 1)',
			'web'
		);
	}

	public function process()
	{
		try {
			$this
				->_setClearStock()
				->_setLocation()
				->_setFilePath()
				->_parseFile()
				->_setUnitLoader()
				->_setStockManager()
				->_clearStock()
				->_saveStockLevels()
				->_commit()
			;
		}
		catch (\Exception $e) {
			$this->writeln('<error>' . $e->getMessage() . '</error>');
		}
	}

	protected function _setClearStock()
	{
		if ($this->getRawInput()->getArgument('clearStock') == '1') {
			$this->writeln('<info>Option set to clear stock levels before running script</info>');
			$this->_clearStock = true;
		}

		return $this;
	}

	public function _setLocation()
	{
		$location        = $this->getRawInput()->getArgument('location') ?: 'web';
		$this->_location = $this->get('stock.locations')->get($location);

		$this->writeln('<info>Setting location to ' . $this->_location->name . '</info>');

		return $this;
	}

	protected function _setFilePath()
	{
		$filepath = $this->getRawInput()->getArgument('file');

		if (!$filepath) {
			throw new \InvalidArgumentException('First argument of file path is required');
		}

		$this->_filepath = $filepath;

		return $this;
	}

	protected function _parseFile()
	{
		$file = fopen($this->_filepath, 'r');

		if ($file) {
			while (false !== ($line = fgets($file))) {
				$line = trim($line);
				$this->writeln('Adding stock for `' . $line . '`');
				if (array_key_exists($line, $this->_barcodeStock)) {
					$this->_barcodeStock[$line]++;
				}
				else {
					$this->_barcodeStock[$line] = 1;
				}
				$this->writeln('Stock for `' . $line . '`: ' . $this->_barcodeStock[$line]);
			}
		}
		else {
			throw new \InvalidArgumentException('File `' . $this->_filepath .'` could not be found');
		}

		return $this;
	}

	protected function _setUnitLoader()
	{
		$this->_unitLoader = $this->get('product.unit.loader')
			->includeOutOfStock(true)
			->includeInvisible(true)
		;

		return $this;
	}

	protected function _setStockManager()
	{
		$this->writeln('Setting stock manager');
		$this->_stockManager = $this->get('stock.manager');

		$reason = new Reason('barcode_task');
		$this->_stockManager->setReason($reason);

		$this->writeln('Stock manager set');

		return $this;
	}

	protected function _clearStock()
	{
		if ($this->_clearStock) {
			$units = $this->_loadAllUnits();
			$this->writeln('<info>Clearing stock for all ' . $this->_location . ' products</info>');

			foreach ($units as $unit) {
				if (!$unit instanceof Unit) {
					throw new \LogicException('$unit must be an instance of Unit, ' . gettype($unit) . ' given');
				}
				$this->writeln('Setting `' . $this->_location->name .'` stock level for unit ' . $unit->id . ' to 0');
				$this->_stockManager->set($unit, $this->_location, 0);
			}
		}
		else {
			$this->writeln('<info>Not clearing stock before running script</info>');
		}

		return $this;
	}

	protected function _saveStockLevels()
	{
		foreach ($this->_barcodeStock as $barcode => $stockLevel) {
			$this->_saveStockForBarcode($barcode, $stockLevel);
		}

		return $this;
	}

	protected function _saveStockForBarcode($barcode, $stockLevel)
	{
		$this->writeln('Setting stock level of `' . $barcode . '` in `' . $this->_location->name . '` to ' . $stockLevel);

		$unit = $this->_unitLoader->getByBarcode($barcode);

		if (!$unit) {
			$this->writeln('<error>Unit with a barcode of `' . $barcode .'` could not be found, so it will be skipped</error>');
		}
		else {
			$this->_stockManager->set($unit, $this->_location, $stockLevel);
		}
	}

	protected function _loadAllUnits()
	{
		$units = [];

		$this->writeln('<info>Loading units from stock table</info>');
		$unitIDs = $this->get('db.query')->run("
			SELECT
				unit_id
			FROM
				product_unit_stock
		")->flatten();

		foreach ($unitIDs as $id) {
			$unit = $this->_unitLoader->getByID($id);

			if ($unit) {
				$units[] = $unit;
			}
			else {
				$this->writeln('<error>Unit ' . $id . ' failed to load, stock has not been reset</error>');
			}
		}

		return $units;
	}

	protected function _commit()
	{
		$this->_stockManager->commit();

		return $this;
	}
}