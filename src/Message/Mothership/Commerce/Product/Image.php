<?php

namespace Message\Mothership\Commerce\Product;

use Message\Mothership\FileManager\File\File;
use Message\Cog\Localisation\Locale;

class Image
{
	public $fileID;
	public $type;
	public $locale;
	public $optionName;
	public $optionValue;
	public $file;

	public function __construct($fileID, $type, Locale $locale, File $file = null, $optionName = null, $optionValue = null)
	{
		$this->fileID = $fileID;
		$this->type = $type;
		$this->locale = $locale;
		$this->file = $file;
		$this->optionName =$optionName;
		$this->optionValue =$optionValue;
	}
}
