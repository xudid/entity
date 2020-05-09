<?php

namespace Entity\Database;

use Doctrine\ORM\EntityManager as EntityManager;
use Doctrine\ORM\EntityRepository;
use Entity\Database\QueryBuilder\DeleteRequest;
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
     * @var string $classNamespace
     **/
    protected string $classNamespace = "";

    private DriverInterface $driver;
    /**
     * @var string
     */
    private string $entitiesDirectory;
    /**
     * @var DataSource
     */
    private DataSource $dataSource;
    /**
     * @var EntityManager|null
     */
    private ?EntityManager $entityManager;
    /**
     * @var EntityRepository
     */
    private ?EntityRepository $repository;

    private $debug = false;


    /**
     * Dao constructor.
     * @param DataSource $dataSource
     * @param string $classNamespace
     * @param string $entitiesDirectory
     * @throws Exception
     */
    function __construct(DataSource $dataSource, string $classNamespace, $entitiesDirectory = '/entities/')
    {
        $this->classNamespace = $classNamespace;
        $this->dataSource = $dataSource;
        $this->driver = $dataSource->getDriver();
        $this->entitiesDirectory = $entitiesDirectory;
    }

    public function enableDebug()
    {
        $this->debug = true;
    }

    /**
     * @param int $id
     * @return object|string|null
     */
    public function delete(int $id)
    {
        try {
            $className = $this->classNamespace;
            $model = new $className([]);
            $request = new DeleteRequest($model::getTableName());
            $request->where('id', '=', $id);
            $result =  $results = (new Executer($this->dataSource, $this->classNamespace,true))->execute($request);
        } catch (Exception $ex) {
            return $ex->getCode() . ' ' . __FILE__;
        }
    }

    /**
     * @return string
     */
    public function getClassNamespace(): string
    {
        return $this->classNamespace;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws Exception
     */
    public function execute(Request $request, string $associationClassName = '')
    {

        try {
            $executer = $this->getExecuter($request, strlen($associationClassName) > 0? $associationClassName :$this->classNamespace);
            if ($this->debug) {
                $executer->enableDebug();
            }
            return $executer->execute();
        } catch (Exception $exception) {
            dump($exception);
        }
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
