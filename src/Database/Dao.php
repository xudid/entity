<?php

namespace Entity\Database;


use Doctrine\ORM\EntityManager as EntityManager;
use Doctrine\ORM\EntityRepository;
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
     * @var DataSource
     */
    private DataSource $dataSource;
    /**
     * @var EntityManager|null
     */
    private ?EntityManager $entityManager;
    /**
     * @var EntityRepository
     */
    private ?EntityRepository $repository;


    /**
     * Dao constructor.
     * @param DataSource $dataSource
     * @param string $classNamespace
     * @param string $entitiesDirectory
     * @throws Exception
     */
    function __construct(DataSource $dataSource, string $classNamespace, $entitiesDirectory = '/entities/')
    {
        $this->classNamespace = $classNamespace;
        $this->dataSource = $dataSource;
        $this->driver = $dataSource->getDriver();
        $this->entitiesDirectory = $entitiesDirectory;
        $this->entityManager = $this->getEntityManager();
        $this->repository = $this->getRepository();
    }

    /**
     * @param $object
     * @return int|mixed
     */
    public function save($object)
    {
        try {
            $this->entityManager->persist($object);
            $this->entityManager->flush();
            return $object;
        } catch (Exception $ex) {
            return $ex->getPrevious()->getCode();
        }
    }

    /**
     * @param $object
     * @return int|mixed
     */
    public function update($object)
    {
        try {
            $this->entityManager->merge($object);
            $this->entityManager->flush();
            return $object;
        } catch (Exception $ex) {
            return $ex->getPrevious()->getCode();
        }
    }

    /**
     * @param int $id
     * @return object|string|null
     */
    public function delete(int $id)
    {
        try {
            $object = $this->repository->find($id);
            $this->entityManager->remove($object);
            $this->entityManager->flush();
            return $object;
        } catch (Exception $ex) {
            return $ex->getPrevious()->getCode() . ' ' . __FILE__;
        }
    }

    /**
     * @return array|object[]|string
     */
    public function findAll()
    {
        try {
            return $this->repository->findAll();
        } catch (Exception $ex) {
            return $ex->getMessage() . ' ' . __FILE__;
        }
    }

    /**
     * @param int $id
     * @return object|string|null
     */
    public function findById(int $id)
    {
        try {
            return $this->repository->find($this->classNamespace, $id);
        } catch (Exception $ex) {
            return $ex->getMessage() . ' ' . __FILE__;
        }
    }

    /**
     * @param array $params
     * @return array|object[]
     * @throws Exception
     */
    public function findBy(array $params)
    {
        try {
            return $this->getRepository()->findBy($params);
        } catch (Exception $ex) {
            var_dump($ex->getMessage());
            throw $ex;
        }
    }

    /**
     * @param string $className
     * @return EntityManager|null
     */
    public function getEntityManager()
    {
        return (new DatabaseAbstractLayer(
            $this->driver,
            $this->entitiesDirectory))
                ->getEntityManager();
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectRepository|\Doctrine\ORM\EntityRepository
     * @throws Exception
     */
    public function getRepository()
    {
        try{
            return $this->getEntityManager()->getRepository($this->classNamespace);
        } catch (Exception $ex) {
        var_dump($ex->getMessage(). ' ' .__FILE__);
        throw $ex;
        }
    }
}
