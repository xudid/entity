<?php

namespace Entity\Database;



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
     * @param $databaseConfig
     * @param string $classnamespace [description]
     * @param string $entitiesDirectory
     */
  function __construct($databaseConfig ,string $classnamespace, $entitiesDirectory = '/entities/')
  {
    $this->classNamespace = $classnamespace;
    $this->databaseConfig = $databaseConfig;
    $this->entitiesDirectory = $entitiesDirectory;
  }

  public function save($object)
  {

    try
    {
      $dbal = new DatabaseAbstractLayer($this->databaseConfig, $this->entitiesDirectory,true);
      $entityManager = $dbal->getEntityManager();
      $entityManager->persist($object);
      $entityManager->flush();
      
      return $object;
    }
    catch(DBALExceptionAlias $ex)
    {
      return $ex->getPrevious()->getCode();
    }
  }

  public function update($object)
  {
    try
    {
      $dbal = new DatabaseAbstractLayerL($this->databaseConfig, $this->entitiesDirectory,true);
      $entityManager = $dbal->getEntityManager();
      $entityManager->merge($object);
      $entityManager->flush();
      return $object;
    }
    catch(DBALExceptionAlias $ex)
    {
      return $ex->getPrevious()->getCode();
    }

  }

  public function delete(int $id)
  {
    try
    {
      $dbal = new DatabaseAbstractLayerL($this->databaseConfig, $this->entitiesDirectory);
      $entityManager = $dbal->getEntityManager();
      $repository = $entityManager->getRepository($this->classNamespace);
      $object = $repository->find($id);
      $entityManager->remove($object);
      $entityManager->flush();
      return $object;
    }
    catch(DBALExceptionAlias $ex)
    {
      return $ex->getPrevious()->getCode();
    }
  }

  public function findAll()
  {
    try
    {
      $dbal = new DatabaseAbstractLayerL($this->databaseConfig, $this->entitiesDirectory);
      $entityManager = $dbal->getEntityManager();
      $result =  ($entityManager->getRepository($this->classNamespace))->findAll();
      return $result;
    }
    catch (DBALExceptionAlias $ex)
    {
      return $ex->getPrevious()->getCode();
    }

  }

  public function findById(int $id)
  {
    try
    {
      $dbal = new DatabaseAbstractLayerL($this->databaseConfig, $this->entitiesDirectory);
      $entityManager = $dbal->getEntityManager();
      $result =  $entityManager->find($this->classNamespace, $id);
      return $result;
    }
    catch (DBALExceptionAlias $ex)
    {

      return $ex->getPrevious()->getCode();
    }

  }

  public function findBy(array $params)
  {
    try
    {
      $dbal = new DatabaseAbstractLayer($this->databaseConfig, $this->entitiesDirectory);
      $entityManager = $dbal->getEntityManager();
      $result =  $entityManager->getRepository($this->classNamespace)->findBy($params);
      return $result;
    }
    catch (DBALExceptionAlias $ex)
    {
      $code = $ex->getPrevious()->getCode();

      return $code;
    }

  }
}
