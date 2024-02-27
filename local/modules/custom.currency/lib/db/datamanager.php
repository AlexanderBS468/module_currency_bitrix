<?php

namespace Custom\Currency\DB;

use Bitrix\Main;
use Bitrix\Main\Entity;

class DataManager extends Entity\DataManager implements DBEntityInterface
{
	/**
	 * @var \Bitrix\Main\ORM\Entity|\Bitrix\Main\ORM\Entity[]
	 */
	protected $entityDB;
	private Main\DB\Connection $connection;

	public function __construct(Main\ORM\Entity $entity)
	{
		$this->entityDB = $entity;
		$this->connection = $entity->getConnection();
	}

	public function install() : void
	{
		$this->entityDB->createDbTable();
	}

	public function uninstall() : void
	{
		$connection = $this->connection;
		$tableName = $this->entityDB->getDBTableName();

		if ($connection->isTableExists($tableName))
		{
			$connection->dropTable($tableName);
		}
	}

	public function truncateTable()
    {
	    $connection = $this->connection;
	    $tableName = $this->entityDB->getDBTableName();

	    if ($connection->isTableExists($tableName))
	    {
			$connection->truncateTable($tableName);
	    }
    }
}
