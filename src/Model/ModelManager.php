<?php

namespace Xudid\Entity\Model;

use Doctrine\Inflector\{Inflector, InflectorFactory, Language};
use Xudid\Entity\Metadata\{Association, DataColumn};
use Exception;
use Xudid\Entity\Model\Proxy\ProxyFactory;
use Xudid\EntityContracts\Request\ExecuterInterface;
use Xudid\EntityContracts\Model\{ManagerInterface, ModelInterface};
use Xudid\QueryBuilder\Request\{Delete, Insert, Select, Update};
use Xudid\QueryBuilderContracts\QueryBuilderInterface;

class ModelManager implements ManagerInterface
{
    private static $inflector;
    protected $modelNamespace;
    protected ExecuterInterface $executer;
    protected QueryBuilderInterface $builder;
    protected bool $lazyLoading = true;
    protected string $proxyCachePath = '';

    public function __construct(string $modelNamespace, QueryBuilderInterface $builder, ExecuterInterface $executer)
    {
        $this->setModelNamespace($modelNamespace);
        $executer->className($modelNamespace);
        $this->executer = $executer;
        $this->builder = $builder;
    }

    public function setProxyCachePath(string $path): static
    {
        $this->proxyCachePath = $path;
        return $this;
    }

    public function debug(): static
    {
        $this->executer->debug();
        return $this;
    }

    public function disableLazyLoading(): static
    {
        $this->lazyLoading = false;
        return $this;
    }

    /**
     * @throws Exception
     */
    public function manage(string $class): static
    {
        if (Model::exists($class)) {
            $executer = clone $this->executer;
            $manager = new ModelManager($class, $this->builder, $executer);
            $manager->setProxyCachePath($this->proxyCachePath);
            return $manager;
        } else {
            throw new Exception($class . ' Is not Model can not manage it ');
        }
    }

    public function setModelNamespace(string $modelNamespace): static
    {
        if (Model::exists($modelNamespace)) {
            $this->modelNamespace = $modelNamespace;
            return $this;
        } else {
            throw new Exception($modelNamespace . ' Is not Model can not manage it ');
        }
    }

    public function builder(): QueryBuilderInterface
    {
        return $this->builder;
    }

    /**
     * @throws Exception
     */
    public function findById($id): ModelInterface
    {
        $request = self::makeFindById($this->modelNamespace, $id);
        $results = $this->executer
            ->request($request)
            ->className($this->modelNamespace)
            ->execute();

        $model = Model::model($this->modelNamespace);
        $results = $this->processResults($results, $model);
        if (is_array($results) && !empty($results)) {
            return $results[0];
        } else {
            return $model;
        }
    }

    /**
     * @throws Exception
     */
    public function findBy(array $params): array
    {
        $request = self::makeFindBy($this->modelNamespace, $params);
        $results = $this->executer
            ->request($request)
            ->className($this->modelNamespace)
            ->execute();
        $model = Model::model($this->modelNamespace);
        if (is_array($results)) {
            return $this->processResults($results, $model);
        } else {
            return [];
        }
    }

    /**
     * @throws Exception
     */
    public function findAll(): array
    {
        $request = self::makeSelectAll($this->modelNamespace);
        $results = $this->executer
            ->request($request)
            ->className($this->modelNamespace)
            ->execute();
        $model = Model::model($this->modelNamespace);
        if (is_array($results)) {
            return $this->processResults($results, $model);
        } else {
            return [];
        }
    }

    public function findAssociationValues(ModelInterface $model, string $associationClassname): array
    {
        $associationModel = (new $associationClassname([]));
        $associationTable = $associationModel::getTable();

        $association = $model::getAssociation($associationClassname);
        if ($association) {
            $table = $model::getTable();
            $type = $association->getType();
        } else {
            $table = '';
            $associations = $associationModel::getAssociations();
            $asso = $associations[strtolower($model::getShortClass())];
            $type = $asso->getType();
            $fk = $asso->getName() . 's_id';
        }
        if ($type == Association::ManyToMany) {
            $junctionTable = $association->getTable();
            return $this->findManyToManyToValues($model, $table, $junctionTable, $associationTable, $associationClassname);
        }
        if ($type == Association::OneToMany) {
            return $this->findOneToManyToValues($model, $table, $associationTable, $associationClassname);
        }

        if (($table && $type == Association::ManyToOne) || ($table && $type == Association::OneToOne)) {
            return $this->findManyToOneValues($model, $table, $associationTable, $associationClassname);
        } elseif ($type == Association::OneToOne) {
            return $this->findOneToOneValues($model, $associationTable, $associationClassname, $fk);
        }
        return [];
    }

    private function findManyToManyToValues(
        ModelInterface $model,
        string         $table,
        string         $junctionTable,
        string         $associationTable,
        string         $associationClassname
    ): array
    {
        $request = (new Select($associationTable . '.*', $junctionTable . '.*'))
            ->from($associationTable)
            ->join($junctionTable, $associationTable . '.id', $associationTable . '_id')
            ->where($table . '_id', '=', $model->getId());
        $results = $this->executer->execute($request, $associationClassname);
        return $returns = $results ? $results : [];
    }

    private function findOneToManyToValues(
        ModelInterface $model,
        string         $table,
        string         $associationTable,
        string         $associationClassname
    ): array
    {
        $request = (new Select($associationTable . '.*'))
            ->from($associationTable)
            ->where($table . '_id', ' = ', $model->getId());
        $results = $this->executer->execute($request, $associationClassname);
        return $results ? $results : [];
    }

    private function findManyToOneValues(
        ModelInterface $model,
        string         $table,
        string         $associationTable,
        string         $associationClassname
    ): array
    {
        $request = (new Select(
            $associationTable . '.*')
        )
            ->from($associationTable)
            ->join($table, $associationTable . '_id', $associationTable . '.id')
            ->where($table . '.id', ' = ', $model->getId());
        $results = $this->executer->request($request)->execute($associationClassname);
        return $results ? $results[0] : [];
    }

    private function findOneToOneValues(
        ModelInterface $model,
        string         $associationTable,
        string         $associationClassname,
        string         $fk
    ): array
    {
        $request = (new Select($associationTable . '.*'))
            ->from($associationTable)
            ->where($fk, ' = ', $model->getId());
        $results = $this->executer->execute($request, $associationClassname);

        return $results;
    }

    public function create(ModelInterface $object): mixed
    {
        $request = self::makeInsert($object);
        $id = $this->executer
            ->request($request)
            ->className($this->modelNamespace)
            ->execute();
        if ($id) {
            $object->setId($id);
        }
        return $object;
    }

    public function update(ModelInterface $object)
    {
        $request = self::makeUpdate($object);
        $this->executer
            ->request($request)
            ->className($this->modelNamespace)
            ->execute();
    }

    public function delete(ModelInterface $object)
    {
        $request = self::makeDelete($object);
        $this->executer->request($request)
            ->className($this->modelNamespace)
            ->execute();
    }

    /**
     * @throws \ReflectionException
     */
    protected static function makeInsert(ModelInterface $object): Insert
    {
        $columns = $object::getColumns();
        $associations = $object::getAssociations();
        $fields = array_keys($columns);

        // add foreign key columns and fields
        foreach ($associations as $association) {
            $toModel = $association->getToModel();
            if (Model::exists($toModel)) {
                $table = $toModel::getTable();
                $associationType = $association->getType();
                if ($associationType == Association::OneToOne || $associationType == Association::ManyToOne) {
                    $fk_name = $table . '_id';
                    $fields[] = $fk_name;
                    $columns[$association->getName()] = new DataColumn($association->getName(), "fk");
                }
            }
        }

        // filter id field and format others fields
        //Todo verify if id column is auto_increment
        //Todo if it is remove it , else use it
        $fields = array_filter($fields, function ($key) {
            if ($key != 'id') {
                return $key;
            }
        });
        $fields = array_map(function ($field) {
            return static::inflector()->tableize($field);
        }, $fields);

        // fill the of values to insert
        $values = [];
        foreach ($columns as $column) {
            $name = $column->getName();
            if ($name == 'id') {
                continue;
            }

            $type = $column->getType();
            $method = static::inflector()->camelize('get' . ucfirst($name));
            if ($type == 'fk') {
                $value = $object->$method()->getId();
                //if ID == 0 makeInsert and put value to new  id;
                $values[static::inflector()->tableize($name) . 's_id'] = $value;
            } else {
                $value = $object->$method();
                $values[$name] = $value;
            }
        }

        $request = new Insert($object::getTable());
        return $request->columns(...$fields)->values(...$values);
    }

    /**
     * @throws \ReflectionException
     */
    protected static function makeUpdate(Model $object): Update
    {
        // update cascade case ?
        $columns = $object::getColumns();
        $associations = $object::getAssociations();

        // add foreign keys fields and columns
        foreach ($associations as $association) {
            $associationType = $association->getType();
            if ($associationType == Association::OneToOne) {
                $columns[$association->getName()] = new DataColumn($association->getName(), "fk");
            }
            if ($associationType == Association::ManyToOne) {
                $columns[$association->getName()] = new DataColumn($association->getName(), "fk");
            }
        }

        $request = new Update($object::getTable());
        foreach ($columns as $column) {
            $name = $column->getName();
            if ($name == 'id') {
                continue;
            }

            $type = $column->getType();
            $method = static::inflector()->camelize('get' . ucfirst($name));
            if ($type == 'fk') {
                $value = $object->$method()->getId();
                $colName = static::inflector()->tableize($name) . 's_id';
                //if ID == 0 makeInsert and put value to new  id;
            } else {
                $value = $object->$method();
                $colName = $name;
            }

            $request->set($colName, $value);
        }
        $request->where('id', '=', $object->getId());

        return $request;
    }

    protected static function makeDelete(Model $object): Delete
    {
        // Add delete cascade case
        $request = new Delete($object::getTable());
        return $request->where('id', '=', $object->getId());
    }

    /**
     * @throws Exception
     */
    protected static function makeSelectAll(string $modelNamespace): Select
    {
        if (Model::exists($modelNamespace)) {
            return (new Select())->from($modelNamespace::getTable());
        }

        throw new Exception('Try to build request on non existing model : ' . $modelNamespace);
    }

    /**
     * @throws Exception
     */
    protected static function makeFindById(string $modelNamespace, int $id): Select
    {
        if (Model::exists($modelNamespace)) {
            $request = (new Select())->from($modelNamespace::getTable());
            return $request->where('id', '=', $id);
        }

        throw new Exception('Try to build request on non existing model : ' . $modelNamespace);

    }

    /**
     * @throws Exception
     */
    protected static function makeFindBy(string $modelNamespace, array $params): Select
    {
        if (Model::exists($modelNamespace)) {
            $request = (new Select())->from($modelNamespace::getTable());
            foreach ($params as $name => $value) {
                $request->where($name, '=', $value);
            }
            return $request;
        }

        throw new Exception('Try to build request on non existing model : ' . $modelNamespace);
    }

    private function lazyLoad(Model $model, Model &$proxy, Association $association)
    {
        $method = 'set' . static::inflector()->classify($association->getName());
        // set closure with affectation code below
        $loader = new LazyLoader(
            $this,
            'findAssociationValuesBy',
            [$association->getToModel(), $model]
        );

        $result = call_user_func_array([$proxy, $method], [$loader]);

    }

    /**
     * @throws Exception
     */
    protected function processResults(array $results, ModelInterface $model): array
    {
        if (empty($results)) {
            return [];
        }
        if ($this->lazyLoading) {
            // Todo do not use setters to init proxy but hydrate proxy with an array of loaders
            // use Illusion class to generate ProxyClasses
            $proxyFactory = new ProxyFactory();
            $proxyFactory->setCachePath($this->proxyCachePath);
        }

        $associations = $model::getAssociations();
        $returns = [];

        foreach ($results as $result) {
            if ($this->lazyLoading) {
                $proxyFactory->setWrapped($result);
            } else {
                $return = $result;
            }
            foreach ($associations as $association) {
                if ($this->lazyLoading) {
                    $loader = $this->getLoader($result, $association);
                    $proxyFactory->addLoader($association, $loader);
                } else {
                    $method = 'set' . static::inflector()->classify($association->getName());
                    $associationValues = $this->findAssociationValues($association->getOutClassName(), $result);
                    if ($associationValues) {
                        $return->$method($associationValues);
                    }
                }
            }
            if ($this->lazyLoading) {
                $return = $proxyFactory->create();
            }
            $returns[] = $return;
        }
        return $returns;
    }

    public function getLoader(Model $model, Association $association)
    {
        return new LazyLoader(
            $this,
            'findAssociationValuesBy',
            [$association->getToModel(), $model]
        );
    }

    public function getModelNamespace()
    {
        return $this->modelNamespace;
    }

    public function setBuilder(QueryBuilderInterface $builder): static
    {
        $this->builder = $builder;
        return $this;
    }

    private static function inflector(): Inflector
    {
        if (static::$inflector == null) {
            static::$inflector = InflectorFactory::createForLanguage(Language::ENGLISH)->build();
        }
        return static::$inflector;
    }

    public function executer(): ExecuterInterface
    {
        return $this->executer;
    }
}
