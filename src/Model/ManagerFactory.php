<?php

namespace Entity\Model;

use Entity\Database\Dao;
use Entity\Database\DataSourceInterface;
use Exception;
use Psr\Log\LoggerInterface;
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
	private ?LoggerInterface $logger;

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
	}

	public function setProxyCachePath(string $path)
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
	}

	/**
	 * @param string $modelNamespace
	 * @return ManagerInterface
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
			} else {
				throw new Exception('failed to build new ModelManager instance');
			}
		}
	}

	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}
}
