<?php

namespace Message\Mothership\Commerce\Order\Entity\Dispatch;

/**
 * Interface defining a dispatch method.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface MethodInterface
{
	/**
	 * Get the name for the dispatch method used internally as an identifier.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Get the name for the dispatch method that is suitable to be displayed to
	 * users.
	 *
	 * @return string
	 */
	public function getDisplayName();

	/**
	 * Given a specific tracking code, this method should return a URL to track
	 * that dispatch using the appropriate website.
	 *
	 * If this dispatch method doesn't support online tracking, this method
	 * should return false.
	 *
	 * @param  string $code The tracking code to get the link for
	 *
	 * @return string|false The URL for the tracking page, or false if it
	 *                      can't be tracked online
	 */
	public function getTrackingLink($code);
}