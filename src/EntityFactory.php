<?php


namespace Entity;


use Entity\Database\DaoInterface;
use Exception;
use ReflectionClass;
use ReflectionException;


class EntityFactory
{
	/**
	 * @var DaoInterface
	 */
	private $daoInterface;
	/**
	 * @var string
	 */
	private $entityClassname;

	/**
	 * EntityFactory constructor.
	 * @param DaoInterface $daoInterface
	 */
	public function __construct(DaoInterface $daoInterface)
	{
		$this->daoInterface = $daoInterface;
	}

	public function getEntity(string $className) {
		$this->entityClassname = DefaultResolver::getEntityClassName($className);
		try {
			$r = new ReflectionClass($this->entityClassname);
			return $r->newInstanceArgs([$this->daoInterface]);
		} catch (ReflectionException $e) {
			throw new Exception($e->getMessage(),$this->entityClassname);
		}
	}
}