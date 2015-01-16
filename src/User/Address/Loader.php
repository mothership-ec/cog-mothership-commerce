<?php

namespace Message\Mothership\Commerce\User\Address;

use Message\Mothership\User\Address\Loader as BaseLoader;

class Loader extends BaseLoader implements \Message\Mothership\Commerce\User\LoaderInterface
{
	protected function _getAddressClassName()
	{
		return 'Message\\Mothership\\Commerce\\User\\Address\\Address';
	}

}