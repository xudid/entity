<?php

namespace Entity\Database;


use Exception;

/**
 * Class Dao
 * @package Entity\Database
 */
class Dao implements DaoInterface
{
    /**
     * @var string $classNamespace
     **/
    protected string $classNamespace = "";

    private DriverInterface $driver;
    /**
     * @var string
     */
    private string $entitiesDirectory;

    /**
     * Dao constructor.
     * @param DriverInterface $dataSource
     * @param string $classnamespace
     * @param string $entitiesDirectory
     */
    function __construct(DataSource $dataSource, string $classnamespace, $entitiesDirectory = '/entities/')
    {
        $this->classNamespace = $classnamespace;
        $this->dataSource = $dataSource;
        $this->driver = $dataSource->getDriver();
        $this->entitiesDirectory = $entitiesDirectory;
    }

    public function save($object)
    {

        try {
            $dbal = new DatabaseAbstractLayer($this->driver, $this->entitiesDirectory, true);
            $entityManager = $dbal->getEntityManager();
            $entityManager->persist($object);
            $entityManager->flush();
            return $object;
        } catch (Exception $ex) {
            return $ex->getPrevious()->getCode();
        }
    }

    public function update($object)
    {
        try {
            $dbal = new DatabaseAbstractLayer($this->driver, $this->entitiesDirectory, true);
            $entityManager = $dbal->getEntityManager();
            $entityManager->merge($object);
            $entityManager->flush();
            return $object;
        } catch (Exception $ex) {
            return $ex->getPrevious()->getCode();
        }
    }

    public function delete(int $id)
    {
        try {
            $dbal = new DatabaseAbstractLayer($this->driver, $this->entitiesDirectory);
            $entityManager = $dbal->getEntityManager();
            $repository = $entityManager->getRepository($this->classNamespace);
            $object = $repository->find($id);
            $entityManager->remove($object);
            $entityManager->flush();
            return $object;
        } catch (Exception $ex) {
            return $ex->getPrevious()->getCode();
        }
    }

    public function findAll()
    {
        try {
            $dbal = new DatabaseAbstractLayer($this->driver, $this->entitiesDirectory);
            $entityManager = $dbal->getEntityManager();
            $result = ($entityManager->getRepository($this->classNamespace))->findAll();
            return $result;
        } catch (Exception $ex) {
            return $ex->getMessage();
        }

    }

    public function findById(int $id)
    {
        try {
            $dbal = new DatabaseAbstractLayer($this->driver, $this->entitiesDirectory);
            $entityManager = $dbal->getEntityManager();
            return $entityManager->find($this->classNamespace, $id);
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function findBy(array $params)
    {
        try {
            $dbal = new DatabaseAbstractLayer($this->driver, $this->entitiesDirectory);
            $entityManager = $dbal->getEntityManager();
            return $entityManager->getRepository($this->classNamespace)->findBy($params);
        } catch (Exception $ex) {
            return $ex->getPrevious()->getCode();
        }
    }
}
