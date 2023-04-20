<?php

namespace Xudid\Entity\Request\Executer;

use Exception;
use Xudid\EntityContracts\Database\Driver\DriverInterface;
use Xudid\EntityContracts\Request\ExecuterInterface;
use Xudid\QueryBuilderContracts\Request\RequestInterface;
use Xudid\QueryBuilderContracts\Request\InsertInterface;
use Xudid\QueryBuilderContracts\Request\UpdateInterface;
use Xudid\QueryBuilderContracts\Request\SelectInterface;
use Xudid\QueryBuilderContracts\Request\DeleteInterface;

/**
 * Class Executer
 */
class Executer implements ExecuterInterface
{
    protected DriverInterface $driver;
    protected string $className = '';
    protected RequestInterface $request;
    protected bool $debug = false;
    protected array $debugData = [];

    /**
     * InsertExecuter constructor.
     */
    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    public function className(string $className): static
    {
        $this->className = $className;
        $this->driver->withClassName($className);
        return $this;
    }

    public function request(RequestInterface $request): static
    {
        $this->request = $request;
        return $this;
    }

    public function debug(): static
    {
        $this->debug = true;
        return $this;
    }

    public function execute()
    {
        try {
            if (!$this->request) {
                throw new Exception('No request to execute');
            }

            if ($this->className) {
                $this->driver
                    ->setFetchMode(DriverInterface::FETCH_CLASS)
                    ->withClassName($this->className);
            }

            $bindResult = $this->driver->bind($this->request);
            $this->debugData['bind_result'] = $bindResult;

            $result = $this->driver->fetchAll();
            return $this->processRequestResult($this->request, $result);
        } catch (Exception $ex) {
            $this->debugData['exception'] = $ex->getMessage();
        }
    }

    public function executeSql(string $sql)
    {
        try {
            $result = $this->driver->query($sql);
            return $result;
        } catch (\DriverException $e) {
        }
    }

    public function debugData(): array
    {
        return $this->debugData;
    }

    private function processRequestResult($request, $result)
    {
        if ($request instanceof InsertInterface) {
            return $this->driver->lastInsertId();
        }

        if (
            $request instanceof DeleteInterface
            ||$request instanceof UpdateInterface
            ||$request instanceof SelectInterface
        ) {
            return $result;
        }
        throw new Exception("Unimplemented SQL request type");
    }
}
