<?php

namespace Xudid\Entity\Model;

use Exception;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Xudid\Entity\Request\Executer\Executer;
use Xudid\EntityContracts\Database\Driver\DriverInterface;
use Xudid\EntityContracts\Model\ManagerInterface;
use Xudid\QueryBuilder\QueryBuilder;

/**
 * Class ManagerFactory
 */
class ManagerFactory
{
	private DriverInterface $driver;
	private string $managerInterfaceName;
	private string $proxyCachePath = '';
	private ?LoggerInterface $logger;
    protected $lazyLoad = false;

	/**
	 * ManagerFactory constructor.
	 */
	public function __construct(DriverInterface $driver)
	{
		$this->driver = $driver;
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

    public function withLazyLoad(): static
    {
        $this->lazyLoad = true;
        return $this;
    }

	/**
	 * @throws Exception
	 */
	public function getManager(string $modelNamespace): ManagerInterface
	{
		try {
			$r = new ReflectionClass($this->managerInterfaceName);
            $builder = new QueryBuilder();
            $executer = new Executer($this->driver);
            $manager = $r->newInstance($modelNamespace, $builder, $executer,);
			$manager->setProxyCachePath($this->proxyCachePath);
            if (!$this->lazyLoad) {
                $manager->disableLazyLoading();
            }
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
