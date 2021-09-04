<?php

namespace Entity\Database\QueryBuilder;

use Entity\Database\DaoInterface;

/**
 * Class Request
 * @package Entity\Database\QueryBuilder
 * @author Didier Moindreau <dmoindreau@gmail.com> on 27/02/2021.
 */
class Request
{
	protected string $TYPE = '';
	protected DaoInterface $databaseAbstractLayer;
	public function setDatabaseAbstractLayer(DaoInterface $databaseAbstractLayer)
	{
		$this->databaseAbstractLayer = $databaseAbstractLayer;
	}

	public function execute()
	{
		return $this->databaseAbstractLayer->execute($this);
	}
}
