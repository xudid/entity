<?php

namespace Entity\Database;

use Doctrine\Common\Inflector\Inflector;
use Entity\Database\QueryBuilder\Request;
use Exception;
use \PDOStatement;

/**
 * Class Executer
 * @package Entity\Database
 */
class Executer implements ExecuterInterface {
    /**
     * @var DriverInterface $driver
     */
    protected DriverInterface $driver;

    /**
     * @var string $className
     */
    protected string $className;

    /**
     * @var PDOStatement $statment
     */
    protected PDOStatement $statment;

    /**
     * @var Request $request
     */
    protected Request $request;

    /**
     * @var bool $debug
     */
    protected bool $debug = false;
    /**
     * @var bool $statmentResult
     */
    protected $statmentResult = false;

    /**
     * InsertExecuter constructor.
     * @param DriverInterface $driver
     */
    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @param string $className
     * @return Executer
     */
    public function className(string $className) : Executer
    {
        $this->className = $className;
        return $this;
    }

    /**
     * @param Request $request
     * @return Executer
     */
    public function request(Request $request) : Executer
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @return Executer
     */
    public function enableDebug()  : Executer
    {
        $this->debug = true;
        return $this;
    }

    public function execute()
    {
        $this->statment = $this->driver->prepare($this->request->query());
        foreach ($this->request->getBinded() as $field => $value) {
            $this->statment->bindValue(':' . Inflector::tableize($field), $value);
        }
        if ($this->debug) {
            $this->statment->debugDumpParams();
        }
        try {
            $this->statmentResult = $this->statment->execute();
            if ($this->debug) {
                $this->statment->debugDumpParams();
            }
        } catch (Exception $ex) {
            dump($ex->getMessage()   . __FILE__);
        }
    }
}
