<?php

namespace Message\Mothership\Commerce\Product\ImageType;

use Message\Cog\Localisation\Locale;
use Message\Cog\ValueObject\Authorship;
use Message\Mothership\Commerce\Product\Pricing;

class ImageType
{
	public $type;

	public function __construct($type)
	{
		$this->type = $type;
	}

    public function __toString()
    {
        return $this->type;
    }
}