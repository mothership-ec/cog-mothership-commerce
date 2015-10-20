<?php

namespace Message\Mothership\Commerce\Product\Barcode\CodeGenerator;

use Message\Cog\ValueObject\Collection;
use Message\Mothership\Commerce\Product\Barcode;

/**
 * Class GeneratorCollection
 * @package Message\Mothership\Commerce\Product\Barcode\CodeGenerator
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Collection for storing instances of GeneratorInterface
 */
class GeneratorCollection extends Collection
{
	/**
	 * @var string
	 */
	private $_default;

	/**
	 * @param array $generators
	 * @param $default
	 */
	public function __construct(array $generators, $default)
	{
		parent::__construct($generators);
		$this->setDefault($default);
	}

	/**
	 * {@inheritDoc}
	 */
	protected function _configure()
	{
		$this->setType('\\Message\\Mothership\\Commerce\\Product\\Barcode\\CodeGenerator\\GeneratorInterface');

		$this->addValidator(function ($item) {
			if (!Barcode\ValidTypes::isValid($item->getBarcodeType())) {
				if (!is_string($item->getBarcodeType())) {
					throw new \InvalidArgumentException(
						'`GeneratorInterface::getBarcodeType()` must return a string, , returns ' . gettype($item->getBarcodeType())
					);
				}
				throw new \LogicException('`' . $item->getBarcodeType() . '` is not a valid barcode type');
			}
		});

		$this->setKey(function ($item) {
			return $item->getName();
		});
	}

	/**
	 * Get a generator that generates barcodes of a specific type. Will return the first it finds, unless $asArray
	 * is set to true, in which case it will return all of them
	 *
	 * @param $type
	 * @param bool $asArray
	 *
	 * @return array | GeneratorInterface
	 */
	public function getByType($type, $asArray = false)
	{
		if (!is_string($type)) {
			throw new \InvalidArgumentException('Type must be a string, ' . gettype($type) . ' given');
		}

		if (!Barcode\ValidTypes::isValid($type)) {
			throw new \LogicException('`' . $type . '` is not a valid barcode type');
		}

		$matches = [];

		foreach ($this as $generator) {
			if ($generator->getBarcodeType() === $type) {
				if (!$asArray) {
					return $generator;
				}

				$matches[$generator->getName()] = $generator;
			}
		}

		if (empty($matches)) {
			throw new \LogicException('No barcode generators of type `' . $type . '` exist on collection');
		}

		return $matches;
	}

	/**
	 * Set which generator to return if none is set in the site config
	 *
	 * @param string | GeneratorInterface  $default
	 */
	public function setDefault($default)
	{
		if (!is_string($default) && !$default instanceof GeneratorInterface) {
			$type = gettype($default) === 'object' ? get_class($default) : gettype($default);
			throw new \InvalidArgumentException('Default must be either a string or instance of GeneratorInterface, ' . $type . ' given');
		}

		$default = ($default instanceof GeneratorInterface) ? $default->getName() : $default;

		if (!$this->exists($default)) {
			throw new \LogicException('`' . $default . '` not set on collection');
		}

		$this->_default = $default;
	}

	/**
	 * Get the default generator
	 *
	 * @return GeneratorInterface
	 */
	public function getDefault()
	{
		return $this->get($this->_default);
	}
}