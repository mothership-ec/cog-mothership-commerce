<?php

namespace Message\Mothership\Commerce\Task\Barcode;

use Message\Cog\Console\Task\Task;

class GenerateAll extends Task
{
	public function process()
	{
		try {
			$this->writeln('Generating barcode images');
			$this->get('product.barcode.generate')->getOneOfEach();
			$this->writeln('Barcodes generated');
		}
		catch (\Exception $e) {
			$this->writeln('<error>' . $e->getMessage() . '</error>');
		}
	}
}