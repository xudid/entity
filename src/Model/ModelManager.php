<?php


namespace Entity\Model;


use Entity\Database\DaoInterface;
use Entity\Database\QueryBuilder\DeleteRequest;
use Entity\Database\QueryBuilder\InsertRequest;
use Entity\Database\QueryBuilder\SelectRequest;
use Entity\Database\QueryBuilder\UpdateRequest;
use Entity\Metadata\Holder\ProxyFactory;

class ModelManager
{
    /**
     * @var DaoInterface
     */
    private DaoInterface $dao;
    private $classNamespace;

    /**
     * ModelManager constructor.
     * @param DaoInterface $dao
     */
    public function __construct(DaoInterface $dao)
    {
        $this->dao = $dao;
    }

    public function findById($id)
    {
        $request = self::makeFindById($this->dao->getClassNamespace(), $id);
        $result = $this->dao->execute($request);
        if ($result) {
            $proxy = new ProxyFactory();
            return $proxy->createProxy($result[0]);
        } else {
           return false;
        }
    }

    public function findBy(array $params)
    {
        $request = self::makeFindBy($this->dao->getClassNamespace(), $params);
        $this->dao->execute($request);
    }

    public function findAll()
    {
        $request = self::makeSelectAll($this->dao->getClassNamespace());
        $this->dao->execute($request);
        $return = [];
        $proxy = new ProxyFactory();
        foreach ($this->dao->execute($request) as $result) {
            $return[] = $proxy->createProxy($result);
        }

        return $return;
    }

    public function findAssociationValuesBy(string $associationClassname, Model $model)
    {
        $associationModel = (new $associationClassname([]));
        $associationTable = $associationModel::getTableName();
        $association = $model::getAssociation($associationClassname);
        $table = $model::getTableName();
        $junctionTable = $association[$associationTable]->getTableName();
        $request = (new SelectRequest($associationTable . '.*'))
            ->from($associationTable)
            ->join($junctionTable,'id',$associationTable. '_id')
            ->where($table . '_id','=',$model->getId());
        return $this->dao->execute($request);
    }

    public function insert($object)
    {
        $request = self::makeInsert($object);
        $id = $this->dao->execute($request);
        if ($id) {
            $object->setId($id);
        }
        return $object;
    }

    public function create($object)
    {
        throw new \Exception('Unimplemented method yet');
       /* $request = self::makeInsert($this->dao->getClassNamespace());
        $this->dao->execute($request);*/
    }

    public function update($object)
    {
        $request = self::makeUpdate($object);
        $this->dao->execute($request);
    }

    public function delete($object)
    {
        $request = self::makeDelete($object);
        $this->dao->execute($request);
    }

    protected static function makeInsert(Model $object) : InsertRequest
    {
        $columns = $object::getColumns();
        $request = new InsertRequest($object::getTableName());
        $fields = array_keys($columns);
        //Todo verify if id column is auto_increment
        //Todo if it is remove it , else use it
        $fields = array_filter($fields, function($key){
            if ($key !='id') {
                return $key;
            }
        });

        $values = [];
        foreach ($columns as $column) {
            $name = $column->getName();
            if ($name !='id') {
                $method = 'get' . $name;
                $value = $object->$method();
                $values[$name] = $value;
            }
        }
       return $request->columns(...$fields)->values($values);
    }

    protected  static function makeUpdate(Model $object) : UpdateRequest
    {
        $columns = $object::getColumns();
        $request = new UpdateRequest($object::getTableName());
        $columns = array_filter($columns, function($key){
            if ($key !='id') {
                return $key;
            }
        },ARRAY_FILTER_USE_KEY);
        foreach ($columns as $column) {
            $name = $column->getName();
            $method = 'get' . $name;
            $request->set($name, $object->$method());
        }
        $request->where('id','=',$object->getId());
        return $request;
    }

    protected  static function makeDelete(Model $object) : DeleteRequest
    {
        $request = new DeleteRequest($object::getTableName());
        return $request->where('id', '=', $object->getId());
    }

    protected static function makeSelectAll(string $classNamespace) : SelectRequest
    {
        $model = (new $classNamespace([]));
        return (new SelectRequest())->from($model::getTableName());
    }

    protected static function makeFindById(string $classNamespace, int $id) : SelectRequest
    {
        $model = (new $classNamespace([]));
        $request = (new SelectRequest())->from($model::getTableName());
        return $request->where('id', '=', $id);
    }

    protected  static function makeFindBy(string $classNamespace, array $params) : SelectRequest
    {
        $model = (new $classNamespace([]));
        $request = (new SelectRequest())->from($model::getTableName());
        foreach ($params as $name => $value) {
            $request->where($name, '=', $value);
        }
        return $request;
    }
}
