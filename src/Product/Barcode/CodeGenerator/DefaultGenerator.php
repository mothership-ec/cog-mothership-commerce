<?php

namespace Message\Mothership\Commerce\Product\Barcode\CodeGenerator;

class DefaultGenerator extends Code39Generator
{
	public function getName()
	{
		return 'default';
	}
}