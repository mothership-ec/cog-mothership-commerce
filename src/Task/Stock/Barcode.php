<?php

namespace Message\Mothership\Commerce\Task\Stock;

use Message\Cog\Console\Task\Task;
use Symfony\Component\Console\Input\InputArgument;

class Barcode extends Task
{
	protected $_filepath;

	/**
	 * @var \Message\Cog\DB\Transaction
	 */
	protected $_transaction;

	protected $_barcodeStock = [];

	protected $_clearStock = false;

	protected $_location = 'web';

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
				->_setTransaction()
				->_clearStock()
				->_saveStockLevels()
				->_commitTransaction()
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
		$location = $this->getRawInput()->getArgument('location');
		if ($location) {
			$this->writeln('<info>Setting location to ' . $location . '</info>');
			$this->_location = (string) $location;
		}

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

	protected function _setTransaction()
	{
		$this->writeln('Setting database transaction');
		$this->_transaction = $this->get('db.transaction');

		return $this;
	}

	protected function _clearStock()
	{
		if ($this->_clearStock) {
			$this->writeln('<info>Clearing stock for all ' . $this->_location . ' products</info>');
			$this->_transaction->add("
				UPDATE
					product_unit_stock
				SET
					stock = :zero?i
				WHERE
					location = :location?s
			", [
				'zero'     => 0,
				'location' => $this->_location,
			]);
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
		$this->writeln('Setting stock level of `' . $barcode . '` in `' . $this->_location . '` to ' . $stockLevel);

		$this->_transaction->add("
			INSERT INTO
				product_unit_stock
				(
					unit_id,
					location,
					stock
				)
			VALUES
				(
					(
						SELECT
							unit_id
						FROM
							product_unit
						WHERE
							barcode = :barcode?s
					),
					:location?s,
					:stockLevel?i
				)
			ON DUPLICATE KEY UPDATE
				stock = :stockLevel?i
		", [
			'stockLevel' => $stockLevel,
			'barcode'    => $barcode,
			'location'   => $this->_location,
		]);

		$this->writeln('Saving snapshot of `' . $barcode . '` stock adjustment');

		$this->_transaction->add("
			INSERT INTO
				product_unit_stock_snapshot
				(
					unit_id,
					location,
					stock,
					created_at
				)
			VALUES
				(
					(
						SELECT
							unit_id
						FROM
							product_unit
						WHERE
							barcode = :barcode?s
					),
					:location?s,
					:stockLevel?i,
					:createdAt?d
				)
		", [
			'stockLevel' => $stockLevel,
			'barcode'    => $barcode,
			'location'   => $this->_location,
			'createdAt'  => new \DateTime,
		]);
	}

	protected function _commitTransaction()
	{
		$this->writeln('Committing transaction');
		$this->_transaction->commit();
		$this->writeln('Transaction committed');

		return $this;
	}
}