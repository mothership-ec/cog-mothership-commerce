<?php

namespace Message\Mothership\Commerce\Product\Image;

use Message\Mothership\Commerce\Product\Product;
use Message\Mothership\Commerce\Product\ProductEntityLoaderInterface;
use Message\Cog\ValueObject\Collection as BaseCollection;

/**
 * Collection of units
 *
 * @author Iris Schaffer <iris@message.co.uk>
 */
class Collection extends BaseCollection
{
	protected function _configure()
	{
		$this->setType('Message\\Mothership\\Commerce\\Product\\Image\\Image');
		$this->setKey('id');

	}

	public function getByType($type = 'default', array $options = null)
	{
		$return  = array();
		$options = (null === $options) ? null : array_filter($options);

		foreach ($this->all() as $image) {
			if ($image->type !== $type) {
				continue;
			}

			if (!is_null($options)) {
				$intersect = array_intersect_assoc($options, $image->options);

				if ($intersect !== $options) {
					continue;
				}
			}

			$return[$image->id] = $image;
		}

		return $return;
	}
}