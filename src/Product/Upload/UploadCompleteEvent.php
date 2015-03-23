<?php

namespace Message\Mothership\Commerce\Product\Upload;

use Message\Cog\Event\Event;

class UploadCompleteEvent extends Event
{
	/**
	 * @var string
	 */
	private $_route;

	/**
	 * @var array
	 */
	private $_params = [];

	/**
	 * @param $route
	 * @throws \InvalidArgumentException
	 *
	 * @return UploadCompleteEvent         return $this for chainability
	 * */
	public function setRoute($route)
	{
		if (!is_string($route)) {
			throw new \InvalidArgumentException('Route must be a string');
		}

		$this->_route = $route;

		return $this;
	}

	/**
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
	public function getRoute()
	{
		return $this->_route;
	}

	/**
	 * @param array $params
	 *
	 * @return UploadCompleteEvent         return $this for chainability
	 */
	public function setParams(array $params)
	{
		$this->_params = $params;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getParams()
	{
		return $this->_params;
	}
}