<?php

namespace Message\Mothership\Commerce\Order\Command;

use Message\Mothership\Commerce\Order\Status;

use Message\Cog\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to list order & order item statuses available to the system.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class StatusList extends Command
{
	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this
			->setName('order:status:list')
			->setDescription('Lists all order & order item statuses available to the system.')
		;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$output->writeln('<info>Order Statuses</info>');
		$this->_outputStatusTable($output, $this->get('order.statuses'));

		$output->writeln('<info>Order Item Statuses</info>');
		$this->_outputStatusTable($output, $this->get('order.item.statuses'));
	}

	/**
	 * Output a table of the statuses for a given status collection.
	 *
	 * @param OutputInterface   $output   The console output stream
	 * @param Status\Collection $statuses The status collection to output
	 */
	protected function _outputStatusTable(OutputInterface $output, Status\Collection $statuses)
	{
		$table = clone $this->getHelperSet()->get('table')
			->setHeaders(array('Code', 'Name'));

		foreach ($statuses as $status) {
			$table->addRow(array($status->code, $status->name));
		}

		$table->render($output);
	}
}
