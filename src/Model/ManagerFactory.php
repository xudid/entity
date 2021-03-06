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
	private string $proxyCachePath;

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

    public function setProxyCachePath(string $path)
	{
		if (is_writable($path)) {
			$this->proxyCachePath = $path;
			return $this;
		}
		throw new Exception('Invalid $ManagerInterface proxy cache path');

	}

    /**
     * @param string $modelNamespace
     * @return ManagerInterface
     * @throws Exception
     */
    public function getManager(string $modelNamespace) : ManagerInterface
    {
        try {
            $dao = new Dao($this->dataSource);
            $r = new ReflectionClass($this->managerInterfaceName);
            $manager =  $r->newInstance($dao, $modelNamespace);
            $manager->setProxyCachePath($this->proxyCachePath);
            return $manager;
        } catch (Exception $e) {
            echo '<pre>' . var_dump($e);
        }

    }
}
