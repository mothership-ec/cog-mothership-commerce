<?php

namespace Message\Mothership\Commerce\Order\Entity\Address;

use Message\Mothership\Commerce\User\Address\Loader as BaseLoader;

/**
 * Class UserAddressLoader
 * @package Message\Mothership\Commerce\Order\Entity\Address
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 *
 * Class for loading addresses from the `user_address` table and binding them to the address order entity.
 * This is different behaviour from the regular `Loader` class in this namespace, as that loads the order entities themselves
 */
class UserAddressLoader extends BaseLoader
{
	protected function _getAddressClassName()
	{
		return 'Message\\Mothership\\Commerce\\Order\\Entity\\Address\\Address';
	}
}