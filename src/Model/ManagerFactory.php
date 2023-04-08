<?php

namespace Xudid\Entity\Model;

use Exception;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Xudid\Entity\Request\Executer\Dao;
use Xudid\EntityContracts\Database\Driver\DataSourceInterface;
use Xudid\EntityContracts\Model\ManagerInterface;

/**
 * Class ManagerFactory
 */
class ManagerFactory
{
	private DataSourceInterface $dataSource;
	private string $managerInterfaceName;
	private string $proxyCachePath;
	private ?LoggerInterface $logger;

	/**
	 * ManagerFactory constructor.
	 */
	public function __construct(DataSourceInterface $dataSource)
	{
		$this->dataSource = $dataSource;
		$this->managerInterfaceName = ModelManager::class;
	}

	/**
	 * @throws Exception
	 */
	public function setManagerInterface(string $managerInterfaceName): static
	{
		if (class_exists($managerInterfaceName)) {
			$this->managerInterfaceName = $managerInterfaceName;
			return $this;
		} else {
			$message = 'Invalid manager interface : ' . $managerInterfaceName;
			if ($this->logger) {
				$this->logger->debug($message);
			} else {
				throw new Exception($message);
			}
		}
        return $this;
	}

    /**
     * @throws Exception
     */
    public function setProxyCachePath(string $path): static
	{
		if (is_writable($path)) {
			$this->proxyCachePath = $path;
			return $this;
		}
		$message = 'Invalid $ManagerInterface proxy cache path';
		if ($this->logger) {
			$this->logger->debug($message);
		} else {
			throw new Exception($message);
		}
        return $this;
	}

	/**
	 * @throws Exception
	 */
	public function getManager(string $modelNamespace): ManagerInterface
	{
		try {
			$dao = new Dao($this->dataSource);
			$r = new ReflectionClass($this->managerInterfaceName);
			$manager = $r->newInstance($dao, $modelNamespace);
			$manager->setProxyCachePath($this->proxyCachePath);
			return $manager;
		} catch (Exception $exception) {
			if ($this->logger) {
				$this->logger->debug($exception->getMessage() . ' failed to build new ModelManager instance');
			}
		}
        throw new Exception('failed to build new ModelManager instance');
	}

	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}
}
