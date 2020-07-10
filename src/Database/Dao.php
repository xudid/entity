<?php

namespace Entity\Database;

use Entity\Database\QueryBuilder\Request;
use Entity\DeleteExecuter;
use Entity\UpdateExecuter;
use Exception;


/**
 * Class Dao
 * @package Entity\Database
 */
class Dao implements DaoInterface
{
    /**
     * @var DriverInterface|mixed
     */
    private DriverInterface $driver;

    /**
     * @var DataSourceInterface $dataSource
     */
    private DataSourceInterface $dataSource;

    /**
     * @var bool $debug
     */
    private bool $debug = false;


    /**
     * Dao constructor.
     * @param DataSourceInterface $dataSource
     */
    function __construct(DataSourceInterface $dataSource)
    {
        $this->dataSource = $dataSource;
        $this->driver = $dataSource->getDriver();
    }

    public function getDatasource()
    {
        return $this->dataSource;
    }

    public function getDriver()
    {
        return $this->driver;
    }

    public function enableDebug()
    {
        $this->debug = true;
    }

    public function beginTransation()
    {
        $this->driver->beginTransaction();
        return $this;
    }


    /**
     * @param Request $request
     * @param string $className
     * @return mixed
     */
    public function execute(Request $request, string $className = '')
    {
        try {
            $executer = $this->getExecuter($request, $className);
            if ($this->debug) {
                $executer->enableDebug();
            }
            return $executer->execute();
        } catch (Exception $exception) {
            dump($exception);
        }
        return false;
    }

    private function getExecuter(Request $request, string $associationClassName) : ExecuterInterface
    {
        switch ($request::TYPE) {
            case 'INSERT':
                $executer = (new InsertExecuter($this->driver))
                    ->className($associationClassName)
                    ->request($request);
                break;
            case 'SELECT':
                $executer = (new SelectExecuter($this->driver))
                    ->className($associationClassName)
                    ->request($request);
                break;
            case 'UPDATE':
                $executer = (new UpdateExecuter($this->driver))
                    ->className($associationClassName)
                    ->request($request);
                break;
            case 'DELETE':
                $executer = (new DeleteExecuter($this->driver))
                    ->className($associationClassName)
                    ->request($request);
                break;
            default:
                throw new Exception("Unimplemented SQL request type");
        }
        if ($this->debug) {
            $executer->enableDebug();
        }
        return $executer;
    }
}
