<?php

namespace Message\Mothership\Commerce\Order\Entity\Document;

use Message\Mothership\Commerce\Order;

use Message\Cog\DB;

/**
 * Order document creator.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Create implements DB\TransactionalInterface
{
	protected $_query;
	protected $_loader;

	public function __construct(DB\Query $query, Loader $loader)
	{
		$this->_query  = $query;
		$this->_loader = $loader;
	}

	public function setTransaction(DB\Transaction $trans)
	{
		$this->_query = $trans;
	}

	public function create(Document $document)
	{
		// Set create authorship data if not already set
		if (!$document->authorship->createdAt()) {
			$document->authorship->create(
				new DateTimeImmutable,
				$this->_currentUser->id
			);
		}

		$this->_validate($document);

		$this->_query->run('
			INSERT INTO
				order_document
			SET
				order_id    = :orderID?i,
				dispatch_id = :dispatchID?in,
				created_at  = :createdAt?i,
				created_by  = :createdBy?in,
				type        = :type?s,
				url         = :url?s
		', array(
			'orderID'    => $document->order->id,
			'dispatchID' => $document->dispatch ? $address->dispatch->id : null,
			'createdAt'  => $document->authorship->createdAt(),
			'createdBy'  => $document->authorship->createdBy(),
			'type'       => $document->type,
			'url'        => $document->file->getPath(),
		));

		if ($this->_query instanceof DB\Transaction) {
			return $document;
		}

		return $this->_loader->getByID($this->_query->id(), $document->order);
	}

	protected function _validate()
	{
		// TODO: check file exists and is valid
		// TODO: check type is valid (where do we define these I wonder? just an array in services?)
	}
}