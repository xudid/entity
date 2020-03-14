<?php


namespace Entity\Database;


use Entity\Database\QueryBuilder\Request;
use PDOStatement;

class Executer implements ExecuterInterface {
    /**
     * @var DriverInterface
     */
    protected DriverInterface $driver;
    protected string $className;
    protected PDOStatement $statment;
    protected Request $request;
    protected bool $debug = false;
    /**
     * @var bool
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
            $this->statment->bindValue(':' . $field, $value);
        }
        if ($this->debug) {
            $this->statment->debugDumpParams();
        }
        try {
            $this->statmentResult = $this->statment->execute();
            if ($this->debug) {
                $this->statment->debugDumpParams();
            }
        } catch (\Exception $ex) {
            dump($ex->getMessage()   . __FILE__);
        }
    }
}
