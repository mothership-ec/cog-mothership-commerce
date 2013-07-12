<?php

namespace Message\Mothership\Commerce\User;

use Message\User\User;

/**
 * Interface for loading decorators for order entities.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface LoaderInterface
{

	public function getbyUser(User $user);
}