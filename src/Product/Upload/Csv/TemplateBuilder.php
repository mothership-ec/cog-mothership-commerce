<?php

namespace Message\Mothership\Commerce\Product\Upload\Csv;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class for creating and force downloading a CSV template file.
 * Users will fill out this file with all the appropriate products and re-upload it to the system, to create
 * multiple products in the system.
 *
 * Class Builder
 * @package Message\Mothership\Commerce\Product\Upload\Csv
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class TemplateBuilder
{
	const FILENAME = 'products';

	/**
	 * @var Columns
	 */
	protected $_columns;

	/**
	 * @var StreamedResponse
	 */
	private $_response;

	public function __construct(Row $columns)
	{
		$this->_columns = $columns;
	}

	/**
	 * Trigger download of the template CSV
	 *
	 * @return StreamedResponse
	 */
	public function download()
	{
		if (null === $this->_response) {
			$this->_setResponse();
		}

		return $this->_response;
	}

	/**
	 * Instanciate the `StreamedResponse` object to create CSV file to download
	 */
	private function _setResponse()
	{
		$columns = $this->_columns->getColumns();
		$fileName = self::FILENAME . '.csv';

		$response = new StreamedResponse(function() use ($columns) {
			$handle = fopen('php://output', 'w');
			fputcsv($handle, $columns);
			fclose($handle);
		});

		$response->headers->set('Content-Type', 'text/csv');
		$response->headers->set('Content-Disposition','attachment; filename="' . $fileName . '"');

		$this->_response = $response;
	}
}