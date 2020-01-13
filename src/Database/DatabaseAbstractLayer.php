<?php

namespace Entity\Database;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\DBAL\Logging\EchoSQLLogger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;

/**
 * Class DatabaseAbstractLayer
 * @package Ui\Model\Database
 * @author Didier Moindreau <dmoindreau@gmail.com> on 01/12/2019.
 */
class DatabaseAbstractLayer
{// Todo refactor in DatabaseDoctrineLayer
  private string $entitiesPath='/';
  private $entityManager=null;
  private $cache = null;
    /**
     * @var DriverInterface
     */
    private DriverInterface $driver;
    /**
     * @var \Doctrine\ORM\Configuration
     */
    private \Doctrine\ORM\Configuration $config;

    function __construct(DriverInterface $driver, $entitiesPath ,$enableLogger=false)
  {
    $isDevMode = true;
    $this->driver = $driver;
    $this->entitiesPath = $entitiesPath;
    $this->cache = $cache = new \Doctrine\Common\Cache\ArrayCache;
    $this->config =   Setup::createConfiguration($isDevMode);
    $annotationDriver = new AnnotationDriver(new AnnotationReader(), $this->entitiesPath);
    // registering noop annotation autoloader - allow all annotations by default
    AnnotationRegistry::registerLoader('class_exists');
    $this->config->setMetadataDriverImpl($annotationDriver);
    /* = Setup::createAnnotationMetadataConfiguration(
      array(__DIR__.$this->entitiesPath), $isDevMode);*/

    $this->config->setMetadataCacheImpl($cache);
    $this->config->setQueryCacheImpl($cache);
    if($enableLogger)
    {
      $logger = new EchoSQLLogger;
      $this->config->setSQLLogger($logger);
    }
      $conn = \Doctrine\DBAL\DriverManager::getConnection(
          [
              'driver' => 'pdo_' . $driver->getAttribute(\PDO::ATTR_DRIVER_NAME),
              'pdo' => $driver,
          ],);
      try {
          $this->entityManager = EntityManager::create($conn, $this->config);
      } catch (ORMException $e) {
          var_dump($e->getMessage());
      }
  }


  public function getEntityManager()
  {
    return $this->entityManager;
  }
}
