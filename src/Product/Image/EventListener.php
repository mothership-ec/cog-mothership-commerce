<?php

namespace Message\Mothership\Commerce\Product\Image;

use Message\Cog\Event\SubscriberInterface;
use Message\Mothership\FileManager\File;
use Message\Mothership\FileManager\File\Event as FileEvent;
use Message\Cog\Event\EventListener as BaseListener;

/**
 * Image event listener.
 *
 * @author Sam Trangmar-Keates <sam@message.co.uk>
 */
class EventListener extends BaseListener implements SubscriberInterface {
	
	/**
	 * {@inheritDoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(
			FileEvent::DELETE => [
				['deleteImage'],
			],
		);
	}

	/**
	 * Delete the given image
	 * @param  Image  $image
	 */
	public function deleteImage(FileEvent $e)
	{
		$images = $this->_services['product.image.loader']
			->getByFile($e->getFile());
		$delete = $this->_services['product.image.delete'];
		
		$delete->delete($images);
	}
}