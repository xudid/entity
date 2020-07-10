<?php

namespace Entity\Model;

use Doctrine\Common\Inflector\Inflector;
use Entity\Database\Dao;
use Entity\Database\DaoInterface;
use Entity\Database\QueryBuilder\DeleteRequest;
use Entity\Database\QueryBuilder\InsertRequest;
use Entity\Database\QueryBuilder\QueryBuilder;
use Entity\Database\QueryBuilder\QueryBuilderInterface;
use Entity\Database\QueryBuilder\SelectRequest;
use Entity\Database\QueryBuilder\UpdateRequest;
use Entity\Metadata\Association;
use Entity\Metadata\DataColumn;
use Entity\Metadata\Holder\ProxyFactory;

class ModelManager implements ManagerInterface
{
    /**
     * @var DaoInterface
     */
    protected DaoInterface $dao;
    protected $classNamespace;
    protected QueryBuilderInterface $builder;

    /**
     * ModelManager constructor.
     * @param DaoInterface $dao
     */
    public function __construct(DaoInterface $dao, string $classNamespace)
    {
        $this->dao = $dao;
        $this->setClassNamespace($classNamespace);
        $this->builder = new QueryBuilder($this->dao->getDatasource());
    }

    public function enableDebug()
    {
        $this->dao->enableDebug();
    }

    public function manage(string $class)
    {
        if(Model::exists($class)) {
            $dao = new Dao($this->dao->getDatasource());
            return new ModelManager($dao, $class);
        } else {
            throw new \Exception($class . ' Is not Model can not manage it ');
        }

    }

    /**
     * @param string $classNamespace
     * @return ModelManager
     */
    public function setClassNamespace(string $classNamespace): ModelManager
    {
        if(Model::exists($classNamespace)) {
            $this->classNamespace = $classNamespace;
            return $this;
        } else {
            throw new \Exception($classNamespace . ' Is not Model can not manage it ');
        }

    }



    /**
     * @return QueryBuilderInterface
     */
    public function builder(): QueryBuilderInterface
    {
        return $this->builder;
    }

    public function findById($id)
    {
        $request = self::makeFindById($this->classNamespace, $id);
        $result = $this->dao->execute($request, $this->classNamespace);
        if ($result) {
            return $result[0];
        } else {
            return false;
        }
    }

    public function findBy(array $params)
    {
        $request = self::makeFindBy($this->classNamespace, $params);
        return $this->dao->execute($request, $this->classNamespace);
    }

    public function findAll()
    {
        $request = self::makeSelectAll($this->classNamespace);
        $return = [];
        $proxy = new ProxyFactory();
        $results = $this->dao->execute($request, $this->classNamespace);
        foreach ($results as $result) {
            //  $return[] = $proxy->createProxy($result);
            $return[] = $result;
        }
        return $return;
    }

    public function findAssociationValuesBy(string $associationClassname, Model $model)
    {
        $associationModel = (new $associationClassname([]));
        $associationTable = $associationModel::getTableName();

        $association = $model::getAssociation($associationClassname);
        if($association) {
            $table = $model::getTableName();
            $type = $association->getType();
        } else {
            $table = '';
            $associations = $associationModel::getAssociations();
            $asso = $associations[strtolower($model::getShortClass())];
            $type = $asso->getType();
            $fk = $asso->getName() . 's_id';
        }
        if ($type == Association::ManyToMany) {
            $junctionTable = $association->getTableName();
            $request = (new SelectRequest($associationTable . '.*', $junctionTable . '.*'))
                ->from($associationTable)
                ->join($junctionTable, $associationTable. '.id', $associationTable . '_id')
                ->where($table . '_id', '=', $model->getId());
            $results = $this->dao->execute($request, $associationClassname);
            return $returns = $results ? $results : [];
        }
        if ($type == Association::OneToMany) {
            $request = (new SelectRequest($associationTable . '.*'))
                ->from($associationTable)
                ->where($table . '_id', ' = ', $model->getId());
            $results = $this->dao->execute($request, $associationClassname);
            return $results ? $results : [];
        }

        if (($table && $type == Association::ManyToOne) || ($table && $type == Association::OneToOne)) {
            $request = (new SelectRequest(
                $associationTable . '.*',
                $table . '.id as ' . $table)
            )
                ->from($associationTable)
                ->join($table, $associationTable . 's_id', $associationTable . '.id')
                ->where($table . '.id', ' = ', $model->getId());
            $results = $this->dao->execute($request, $associationClassname);
            return $results ? $results[0] : false;
        } elseif ($type == Association::OneToOne) {
            $request = (new SelectRequest( $associationTable . '.*'))
                ->from($associationTable)
                ->where($fk, ' = ', $model->getId());
            $results = $this->dao->execute($request, $associationClassname);
            return $results ?: false;
        }

    }

    public function insert($object)
    {
        $request = self::makeInsert($object);
        $id = $this->dao->execute($request, $this->classNamespace);
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
        $this->dao->execute($request, $this->classNamespace);
    }

    public function delete($object)
    {
        $request = self::makeDelete($object);
        $this->dao->execute($request, $this->classNamespace);
    }

    protected static function makeInsert(Model $object): InsertRequest
    {
        $columns = $object::getColumns();
        $associations = $object::getAssociations();
        $request = new InsertRequest($object::getTableName());
        $fields = array_keys($columns);
        foreach ($associations as $association) {
            $outAssociationName = $association->getOutClassName();

            if (Model::exists($outAssociationName)) {
                $table = $outAssociationName::getTableName();
                $associationType = $association->getType();
                if ($associationType == Association::OneToOne) {
                    $fk_name = $table . '_id';
                    $fields[] = $fk_name;
                    $columns[$association->getName()] = new DataColumn($association->getName(), "fk");
                }
                if ($associationType == Association::ManyToOne) {
                    $fk_name = $table . '_id';
                    $fields[] = $fk_name;
                    $columns[$association->getName()] = new DataColumn($association->getName(), "fk");
                }
            }
        }
        //Todo verify if id column is auto_increment
        //Todo if it is remove it , else use it
        $fields = array_filter($fields, function ($key) {
            if ($key != 'id') {
                return $key;
            }

        });
        $fields = array_map(function ($field) {
            return Inflector::tableize($field);
        }, $fields);
        $values = [];
        foreach ($columns as $column) {
            $name = $column->getName();
            $type = $column->getType();
            if ($name != 'id') {
                $method = Inflector::camelize('get' . ucfirst($name));
                if ($type == 'fk') {
                    $value = $object->$method()->getId();
                    //if ID == 0 makeInsert and put value to new  id;
                    $values[Inflector::tableize($name) . 's_id'] = $value;
                } else {
                    $value = $object->$method();
                    $values[$name] = $value;
                }

            }
        }
        return $request->columns(...$fields)->values($values);
    }

    protected static function makeUpdate(Model $object): UpdateRequest
    {
        $columns = $object::getColumns();
        $request = new UpdateRequest($object::getTableName());
        $columns = array_filter($columns, function ($key) {
            if ($key != 'id') {
                return $key;
            }
        }, ARRAY_FILTER_USE_KEY);
        foreach ($columns as $column) {
            $name = $column->getName();
            $method = 'get' . $name;
            $request->set($name, $object->$method());
        }
        $request->where('id', '=', $object->getId());
        return $request;
    }

    protected static function makeDelete(Model $object): DeleteRequest
    {
        $request = new DeleteRequest($object::getTableName());
        return $request->where('id', '=', $object->getId());
    }

    protected static function makeSelectAll(string $classNamespace): SelectRequest
    {
        if (Model::exists($classNamespace)) {
            return (new SelectRequest())->from($classNamespace::getTableName());
        }
        throw new \Exception('Try to build request on non existing model : ' . $classNamespace);
    }

    protected static function makeFindById(string $classNamespace, int $id): SelectRequest
    {
        if (Model::exists($classNamespace)) {
            $request = (new SelectRequest())->from($classNamespace::getTableName());
            return $request->where('id', '=', $id);
        }
        throw new \Exception('Try to build request on non existing model : ' . $classNamespace);

    }

    protected static function makeFindBy(string $classNamespace, array $params): SelectRequest
    {
        if (Model::exists($classNamespace)) {
            $request = (new SelectRequest())->from($classNamespace::getTableName());
            foreach ($params as $name => $value) {
                $request->where($name, '=', $value);
            }
            return $request;
        }
        throw new \Exception('Try to build request on non existing model : ' . $classNamespace);
    }
}
