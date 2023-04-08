<?php

namespace Xudid\Entity\Request\Executer;

use Exception;
use Xudid\EntityContracts\Database\Driver\DaoInterface;
use Xudid\EntityContracts\Database\Driver\DataSourceInterface;
use Xudid\EntityContracts\Database\Driver\DriverInterface;
use Xudid\EntityContracts\Executer\ExecuterInterface;
use Xudid\QueryBuilderContracts\Request\DeleteInterface;
use Xudid\QueryBuilderContracts\Request\InsertInterface;
use Xudid\QueryBuilderContracts\Request\RequestInterface;
use Xudid\QueryBuilderContracts\Request\SelectInterface;
use Xudid\QueryBuilderContracts\Request\UpdateInterface;

/**
 * Class Dao
 */
class Dao implements DaoInterface
{
    private DriverInterface $driver;
    private DataSourceInterface $dataSource;
    private bool $debug = false;


    /**
     * Dao constructor.
     */
    function __construct(DataSourceInterface $dataSource)
    {
        $this->dataSource = $dataSource;
        $this->driver = $dataSource->getDriver();
    }

    public function getDatasource(): DataSourceInterface
    {
        return $this->dataSource;
    }

    public function getDriver(): DriverInterface
    {
        return $this->driver;
    }

    public function debug(): static
    {
        $this->debug = true;
        return $this;
    }

    public function execute(RequestInterface $request, string $className = '')
    {
        try {
            $executer = $this->getExecuter($request, $className);
            if ($this->debug) {
                $executer->debug();
            }
            return $executer->execute();
        } catch (Exception $exception) {
        }
        return false;
    }

    private function getExecuter(RequestInterface $request, string $associationClassName) : ExecuterInterface
    {
        switch (get_class($request)) {
            case InsertInterface::class:
                $executer = (new InsertExecuter($this->driver))
                    ->className($associationClassName)
                    ->request($request);
                break;
            case SelectInterface::class:
                $executer = (new SelectExecuter($this->driver))
                    ->className($associationClassName)
                    ->request($request);
                break;
            case UpdateInterface::class:
                $executer = (new UpdateExecuter($this->driver))
                    ->className($associationClassName)
                    ->request($request);
                break;
            case DeleteInterface::class:
                $executer = (new DeleteExecuter($this->driver))
                    ->className($associationClassName)
                    ->request($request);
                break;
            default:
                throw new Exception("Unimplemented SQL request type");
        }
        if ($this->debug) {
            $executer->debug();
        }
        return $executer;
    }
}
