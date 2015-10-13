<?php

namespace Message\Mothership\Commerce\Task\Product;

use Message\Cog\Console\Task\Task;

/**
 * Class DeleteOptionlessUnits
 * @package Message\Mothership\Commerce\Task\Product
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Task for deleting units that have no options assigned to them. It was previously possible to create units
 * with no options, which would break on the unit edit screen. This task marks any of those units as deleted.
 */
class DeleteOptionlessUnits extends Task
{
	/**
	 * {@inheritDoc}
	 */
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