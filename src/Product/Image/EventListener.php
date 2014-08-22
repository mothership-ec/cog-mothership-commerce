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
				['deleteImages'],
			],
		);
	}

	/**
	 * Delete the given image
	 * @param  FileEvent  $e
	 */
	public function deleteImages(FileEvent $e)
	{
		$images = $this->_services['product.image.loader']
			->getByFile($e->getFile());
		if (!empty($images)){
			$delete = $this->_services['product.image.delete'];	
			$delete->delete($images);
		}
	}
}