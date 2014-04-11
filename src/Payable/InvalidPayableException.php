<?php

namespace Message\Mothership\Commerce\Payable;

use Exception;
use InvalidArgumentException;

/**
 * Exception thrown when a payable is deemed invalid by a validator.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class InvalidPayableException extends InvalidArgumentException
{
	/**
	 * List of errors created by an invalid payable.
	 *
	 * @var array
	 */
	protected $_errors;

	/**
	 * Construct the exception with an array of error messages.
	 *
	 * @param array     $errors
	 * @param integer   $code
	 * @param Exception $previous
	 */
	public function __construct(array $errors, $code = 0, Exception $previous = null)
	{
		parent::_construct(null, $code, $previous);

		$this->_errors = $errors;
	}

	/**
	 * Get the list of errors that were created by an invalid payable.
	 *
	 * @return array
	 */
	public function getErrors()
	{
		return $this->_errors;
	}
}