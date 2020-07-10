<?php

namespace Entity\Model;

use Entity\Database\Dao;
use Entity\Database\DataSourceInterface;
use Exception;
use ReflectionClass;

/**
 * Class ManagerFactory
 * @package Entity\Model
 */
class ManagerFactory
{
    /**
     * @var DataSourceInterface
     */
    private DataSourceInterface $dataSource;
    /**
     * @var string
     */
    private string $managerInterfaceName;

    /**
     * ManagerFactory constructor.
     * @param DataSourceInterface $dataSource
     */
    public function __construct(DataSourceInterface $dataSource)
    {
        $this->dataSource = $dataSource;
        $this->managerInterfaceName = ModelManager::class;
    }

    /**
     * @param string $managerInterfaceName
     * @return ManagerFactory
     * @throws Exception
     */
    public function setManagerInterface(string $managerInterfaceName)
    {
        if (class_exists($managerInterfaceName))
        {
            $this->managerInterfaceName = $managerInterfaceName;
            return $this;
        } else {
            throw new Exception('Invalid manager interface : ' . $managerInterfaceName);
        }
    }

    /**
     * @param string $className
     * @return ManagerInterface
     * @throws Exception
     */
    public function getManager(string $className) : ManagerInterface
    {
        try {
            $dao = new Dao($this->dataSource);
            $r = new ReflectionClass($this->managerInterfaceName);
            return $r->newInstance($dao, $className);
        } catch (Exception $e) {
            dump($e);
        }

    }
}
