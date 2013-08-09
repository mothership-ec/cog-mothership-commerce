<?php

namespace Message\Mothership\Commerce\Order;

/**
 * Order metadata, responsible for storing arbitrary key/value data about a
 * specific order.
 *
 * This class is not responsible for the loading, saving or updating of metadata.
 * The order Loader, Create and Edit decorators are responsible for this.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Metadata implements \ArrayAccess, \IteratorAggregate, \Countable
{
	protected $_data        = array();
	protected $_removedKeys = array();

	/**
	 * Output the metadata as a string.
	 *
	 * @return string The metadata as a single string
	 */
	public function __toString()
	{
		$return = '';

		foreach ($this->_data as $key => $value) {
			$return .= $key . ' = ' . $value . "\n";
		}

		return $return;
	}

	/**
	 * Magic getter, enables getting a metadata item as an object property.
	 *
	 * @see get
	 *
	 * @param  string $name The metadata key name to get the value for
	 *
	 * @return mixed        Metadata value
	 */
	public function __get($name)
	{
		return $this->get($name);
	}

	/**
	 * Magic isset, enabled checking if a metadata item is set as an object
	 * property.
	 *
	 * @see exists
	 *
	 * @param  string  $name The metadata key name to check
	 *
	 * @return boolean       True if the metadata item is set, false otherwise
	 */
	public function __isset($name)
	{
		return $this->exists($name);
	}

	/**
	 * Magic setter, enables setting a metadata item as an object property.
	 *
	 * @see set
	 *
	 * @param string $name  The metadata key name to set
	 * @param mixed  $value The metadata value
	 */
	public function __set($name, $value)
	{
		$this->set($name, $value);
	}

	/**
	 * Check if a metadata key is set.
	 *
	 * @param  string $name The metadata key to check for
	 *
	 * @return boolean      True if it's set, false otherwise
	 */
	public function exists($name)
	{
		return array_key_exists($name, $this->_data);
	}

	/**
	 * Get a metadata value.
	 *
	 * @param  string $name The metadata key name
	 *
	 * @return mixed        The metadata value
	 *
	 * @throws \InvalidArgumentException If the metadata key has not been set
	 */
	public function get($name)
	{
		if (!$this->exists($name)) {
			throw new \InvalidArgumentException(sprintf('Order metadata key `%s` not set', $name));
		}

		return $this->_data[$name];
	}

	/**
	 * Set a metadata value.
	 *
	 * @param string $name  The metadata key name
	 * @param mixed  $value The metadata value
	 *
	 * @throws \InvalidArgumentException If the metadata key has already been set
	 */
	public function set($name, $value)
	{
		if ($this->exists($name)) {
			throw new \InvalidArgumentException(sprintf('Cannot set order metadata key `%s` as it has already been set', $name));
		}

		$this->_data[$name] = $value;
	}

	/**
	 * Remove a metadata item.
	 *
	 * The removed metadata key is added to an internal array of removed keys
	 * that can be accessed using `getRemovedKeys()`.
	 *
	 * @param  string $name The metadata key name
	 *
	 * @throws \InvalidArgumentException If the metadata key has not been set
	 */
	public function remove($name)
	{
		if (!$this->exists($name)) {
			throw new \InvalidArgumentException(sprintf('Cannot remove order metadata key `%s` as it is not set', $name));
		}

		unset($this->_data[$name]);

		$this->_removedKeys[$name] = $name;
	}

	/**
	 * Get the number of metadata items.
	 *
	 * @return int
	 */
	public function count()
	{
		return count($this->_data);
	}

	/**
	 * Get all metadata items as an associative array.
	 *
	 * @return array All metadata items
	 */
	public function all()
	{
		return $this->_data;
	}

	/**
	 * Check if a metadata item exists using array syntax.
	 *
	 * @see exists
	 *
	 * @param  string $name The metadata key name
	 *
	 * @return boolean      True if it's set, false otherwise
	 */
	public function offsetExists($name)
	{
		return $this->exists($name);
	}

	/**
	 * Get a metadata value using array syntax.
	 *
	 * @see get
	 *
	 * @param  string $name The metadata key
	 *
	 * @return mixed        The metadata value
	 */
	public function offsetGet($name)
	{
		return $this->get($name);
	}

	/**
	 * Set a metadata value using array syntax.
	 *
	 * @see set
	 *
	 * @param  string $name  The metadata key name
	 * @param  mixed  $value The metadata value
	 */
	public function offsetSet($name, $value)
	{
		$this->set($name, $value);
	}

	/**
	 * Remove a metadata item using a using array syntax.
	 *
	 * @see remove
	 *
	 * @param  string $name The metadata key name
	 */
	public function offsetUnset($name)
	{
		$this->remove($name);
	}

	/**
	 * Get array of metadata keys that have been removed.
	 *
	 * @return array Array of removed metadata keys
	 */
	public function getRemovedKeys()
	{
		return $this->_removedKeys;
	}

	/**
	 * Get the iterator to use when iterating over this class.
	 *
	 * @return \ArrayIterator Array iterator for the metadata array
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->_data);
	}
}