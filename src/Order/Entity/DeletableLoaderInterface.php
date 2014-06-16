<?php

namespace Message\Mothership\Commerce\Order\Entity;

/**
 * Interface for loading decorators for deletable order entities.
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
interface DeletableLoaderInterface extends LoaderInterface
{
	/**
	 * Sets whether loader should also load deleted entities or not.
	 *
	 * @param  bool                     $bool  Whether the loader should include
	 *                                         deleted entities or not. Defaults to true.
	 *                                         
	 * @return DeletableLoaderInterface        DeletableLoaderInterface object in order to chain the methods
	 */
	public function includeDeleted($bool = true);
}