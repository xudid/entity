<?php

namespace Xudid\Entity\Request\Executer;

use App\DB\MysqlPDODriver;
use Doctrine\Inflector\Inflector;
use PDO;
use \PDOStatement;
use Xudid\EntityContracts\Database\Driver\DriverInterface;
use Xudid\EntityContracts\Request\ExecuterInterface;
use Xudid\QueryBuilderContracts\Request\RequestInterface;

/**
 * Class Executer
 */
class Executer implements ExecuterInterface
{
    protected DriverInterface $driver;
    protected string $className;
    protected PDOStatement $statment;
    protected RequestInterface $request;
    protected bool $debug = false;
    protected $statmentResult = false;
    protected $connexion;
    private array $debugData = [];

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
            if ($this->className) {
                $this->driver
                    ->setFetchMode(DriverInterface::FETCH_CLASS)
                    ->withClassName($this->className);
            }
            $bindResult = $this->driver->bind($this->request);
            $this->debugData['bind_result'] = $bindResult;
            return $this->driver->fetchAll();
        } catch (Exception $ex) {
            $this->debugData['exception'] = $ex->getMessage();
        }

/*
        if ($this->debug) {
            $this->statment->debugDumpParams();
        }
        try {
            $this->statmentResult = $this->statment->execute();
            if ($this->debug) {
                $this->statment->debugDumpParams();
                $this->debugData = [
                    'result' => $this->statmentResult,
                    'row count' => $this->statment->rowCount(),
                    'error code' => $this->statment->errorCode(),
                    'error info 1' => $this->statment->errorInfo()[0],
                    'error info 2' => $this->statment->errorInfo()[1],
                    //'uniq_id' => $this->id,
                ];
            }
        } catch (\PDOException $ex) {
            $this->debugData['exception'] = $ex->getMessage() . __FILE__;
        }*/
    }

    public function executeSql(string $sql)
    {
        $statement = $this->connexion->query($sql);
        if (!$statement) {

        }

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function debugData(): array
    {
        return $this->debugData;
    }
}
