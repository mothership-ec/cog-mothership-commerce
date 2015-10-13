<?php

namespace Message\Mothership\Commerce\Task\Product;

use Message\Cog\Console\Task\Task;

/**
 * Class DeleteOptionlessUnits
 * @package Message\Mothership\Commerce\Task\Product
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 */
class DeleteOptionlessUnits extends Task
{
	public function process()
	{
		$units = $this->get('product.unit.loader')
			->includeInvisible()
			->includeOutOfStock()
			->includeDeleted(false)
			->getAll()
		;

		$this->writeln('Loaded ' . count($units) . ' units');

		$invalid = [];

		foreach ($units as $unit) {
			if (count($unit->options) === 0) {
				$this->writeln('<info>Unit ' . $unit->id . ' has no options, marking for deletion</info>');
				$invalid[] = $unit;
			}
		}

		$this->writeln('<info>Found ' . count($invalid) . ' invalid units</info>');

		foreach ($invalid as $unit) {
			$this->writeln('<info>Deleting unit ' . $unit->id . '</info>');
			$this->get('product.unit.delete')->delete($unit);
			$this->writeln('<info>Unit ' . $unit->id . ' deleted</info>');
		}

		$this->writeln('<info>Deletion completion</info>');
	}
}