<?php

namespace Entity\Database;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Logging\EchoSQLLogger;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;

/**
 * Class DatabaseAbstractLayer
 * @package Ui\Model\Database
 * @author Didier Moindreau <dmoindreau@gmail.com> on 01/12/2019.
 */
class DatabaseAbstractLayer
{// Todo refactor in DatabaseDoctrineLayer
    private string $entitiesPath = '/';
    private $entityManager = null;
    private $cache = null;
    /**
     * @var DriverInterface
     */
    private DriverInterface $driver;
    /**
     * @var Configuration
     */
    private Configuration $config;

    function __construct(DriverInterface $driver, $entitiesPath, $enableLogger = true)
    {
        $isDevMode = true;

        $this->driver = $driver;
        $this->entitiesPath = $entitiesPath;

        $this->cache = $cache = new ArrayCache;

        $this->config = Setup::createConfiguration($isDevMode);
        $annotationDriver = new AnnotationDriver(new AnnotationReader(), $this->entitiesPath);
        // registering noop annotation autoloader - allow all annotations by default
        AnnotationRegistry::registerLoader('class_exists');
        $this->config->setMetadataDriverImpl($annotationDriver);
        $this->config->setMetadataCacheImpl($cache);
        $this->config->setQueryCacheImpl($cache);

        if ($enableLogger) {
            $logger = new EchoSQLLogger;
            $this->config->setSQLLogger($logger);
        }


        try {
            $conn = DriverManager::getConnection(
                [
                    'driver' => 'pdo_' . $driver->getAttribute(\PDO::ATTR_DRIVER_NAME),
                    'pdo' => $driver,
                ]
            );
            $this->entityManager = EntityManager::create($conn, $this->config);
        } catch (ORMException $e) {
            var_dump($e->getMessage());
            var_dump($e->getPrevious()->getMessage() . ' ' . __FILE__);
        } catch (DBALException $e) {
            var_dump($e->getMessage());
            var_dump($e->getPrevious()->getMessage() . ' ' . __FILE__);
        }
    }


    public function getEntityManager()
    {
        return $this->entityManager;
    }
}
