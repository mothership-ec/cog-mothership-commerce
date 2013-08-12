<?php

namespace Message\Mothership\Commerce\Order\Entity\Item;

/**
 * Represents an item row, where multiple items for the same unit are displayed
 * as a single row with a quantity.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Row implements \IteratorAggregate, \Countable
{
	protected $_items = array();

	/**
	 * Add an item to this row.
	 *
	 * The item must have the same unit ID as every other item in this row or
	 * an exception will be thrown.
	 *
	 * @param Item $item The item to add
	 *
	 * @throws \InvalidArgumentException If the item's unit ID is different to
	 *                                   the other unit IDs in this row
	 */
	public function add(Item $item)
	{
		if ($this->count() > 0) {
			$firstItem = reset($this->_items);
			if ($item->unitID !== $firstItem->unitID) {
				throw new \InvalidArgumentException(sprintf(
					'Cannot add item to row as it has a different unit ID: `%s` and `%s`',
					$firstItem->unitID,
					$item->unitID
				));
			}
		}

		$this->_items[] = $item;
	}

	/**
	 * Get the sum of the values of a particular property on all the items in
	 * this row.
	 *
	 * @param  string $property The property name to sum
	 *
	 * @return float|int        The sum of the property values in this row.
	 *
	 * @throws \BadMethodCallException   If no items have been set on this row yet
	 * @throws \InvalidArgumentException If the property does not exist on any
	 *                                   of the items
	 */
	public function sum($property)
	{
		if ($this->count() < 1) {
			throw new \BadMethodCallException(sprintf(
				'Cannot sum `%s` property for item row: no items have been set',
				$property
			));
		}

		$return = 0;

		foreach ($this->_items as $item) {
			if (!property_exists($item, $property)) {
				throw new \InvalidArgumentException(sprintf(
					'Cannot sum `%s` property for item row: property does not exist',
					$property
				));
			}

			$return += $item->{$property};
		}

		return $return;
	}

	/**
	 * Collapse the values of a particular property on all the items in this row
	 * into a comma-separated string.
	 *
	 * Duplicates are removed unless the second argument is passed as `true`.
	 *
	 * @param  string  $property        The property name to collapse
	 * @param  boolean $allowDuplicates True to allow duplicate values, false
	 *                                  otherwise
	 *
	 * @return string                   The values collapsed as a string
	 *
	 * @throws \BadMethodCallException   If no items have been set on this row yet
	 * @throws \InvalidArgumentException If the property does not exist on any
	 *                                   of the items
	 */
	public function collapse($property, $allowDuplicates = false)
	{
		if ($this->count() < 1) {
			throw new \BadMethodCallException(sprintf(
				'Cannot sum `%s` property for item row: no items have been set',
				$property
			));
		}

		$return = array();

		foreach ($this->_items as $item) {
			if (!property_exists($item, $property)) {
				throw new \InvalidArgumentException(sprintf(
					'Cannot sum `%s` property for item row: property does not exist',
					$property
				));
			}

			// Only add the value if it's unique or duplicates are allowed
			if ($allowDuplicates || !in_array($item->{$property}, $return)) {
				$return[] = $item->{$property};
			}
		}

		return implode(', ', $return);
	}

	/**
	 * @see count
	 */
	public function getQuantity()
	{
		return $this->count();
	}

	/**
	 * Get the number of items in this row.
	 *
	 * @return int
	 */
	public function count()
	{
		return count($this->_items);
	}

	/**
	 * Get the iterator to use when iterating over this class.
	 *
	 * @return \ArrayIterator Iterator for the items in this row
	 */
	public function getIterator()
	{
		return \ArrayIterator($this->_items);
	}
}