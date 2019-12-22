<?php

namespace Entity\Database;


use Doctrine\DBAL\DBALException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use Ui\Model\Database\DataSourceInterface;

/**
 * Class Dao
 * @package Entity\Database
 */
class Dao implements DaoInterface
{
    /**
     * @var string $classNamespace
     */
    protected $classNamespace = "";

    private $databaseConfig;
    /**
     * @var string
     */
    private $entitiesDirectory;

    /**
     * [__construct description]
     * @param DataSourceInterface $dataSource
     * @param string $classnamespace [description]
     * @param string $entitiesDirectory
     */
    function __construct(DataSourceInterface $dataSource, string $classnamespace, $entitiesDirectory = '/entities/')
    {
        $this->classNamespace = $classnamespace;
        $this->databaseConfig = $dataSource->getConfig();
        $this->entitiesDirectory = $entitiesDirectory;
    }

    public function save($object)
    {

        try {
            $dbal = new DatabaseAbstractLayer($this->databaseConfig, $this->entitiesDirectory, true);
            $entityManager = $dbal->getEntityManager();
            $entityManager->persist($object);
            $entityManager->flush();
            return $object;
        } catch (DBALException $ex) {
            return $ex->getPrevious()->getCode();
        } catch (ORMException $e) {
            return $e->getPrevious()->getCode();
        }
    }

    public function update($object)
    {
        try {
            $dbal = new DatabaseAbstractLayer($this->databaseConfig, $this->entitiesDirectory, true);
            $entityManager = $dbal->getEntityManager();
            $entityManager->merge($object);
            $entityManager->flush();
            return $object;
        } catch (DBALException $ex) {
            return $ex->getPrevious()->getCode();
        } catch (ORMException $e) {
        }
    }

    public function delete(int $id)
    {
        try {
            $dbal = new DatabaseAbstractLayer($this->databaseConfig, $this->entitiesDirectory);
            $entityManager = $dbal->getEntityManager();
            $repository = $entityManager->getRepository($this->classNamespace);
            $object = $repository->find($id);
            $entityManager->remove($object);
            $entityManager->flush();
            return $object;
        } catch (DBALException $ex) {
            return $ex->getPrevious()->getCode();
        } catch (ORMException $e) {
        }
    }

    public function findAll()
    {
        try {
            $dbal = new DatabaseAbstractLayer($this->databaseConfig, $this->entitiesDirectory);
            $entityManager = $dbal->getEntityManager();
            $result = ($entityManager->getRepository($this->classNamespace))->findAll();
            return $result;
        } catch (DBALException $ex) {
            return $ex->getPrevious()->getCode();
        }

    }

    public function findById(int $id)
    {
        try {
            $dbal = new DatabaseAbstractLayer($this->databaseConfig, $this->entitiesDirectory);
            $entityManager = $dbal->getEntityManager();
            return $entityManager->find($this->classNamespace, $id);
        } catch (DBALException $ex) {
            return $ex->getPrevious()->getCode();
        } catch (OptimisticLockException $e) {
            return $e->getPrevious()->getCode();
        } catch (TransactionRequiredException $e) {
            return $e->getPrevious()->getCode();
        } catch (ORMException $e) {
            return $e->getPrevious()->getCode();
        }
    }

    public function findBy(array $params)
    {
        try {
            $dbal = new DatabaseAbstractLayer($this->databaseConfig, $this->entitiesDirectory);
            $entityManager = $dbal->getEntityManager();
            return $entityManager->getRepository($this->classNamespace)->findBy($params);
        } catch (DBALException $ex) {
            return $ex->getPrevious()->getCode();
        }
    }
}
