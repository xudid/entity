<?php

namespace Entity\Model;

use Doctrine\Common\Inflector\Inflector;
use Entity\Database\Dao;
use Entity\Database\DaoInterface;
use Entity\Database\LazyLoader;
use Entity\Database\QueryBuilder\DeleteRequest;
use Entity\Database\QueryBuilder\InsertRequest;
use Entity\Database\QueryBuilder\QueryBuilder;
use Entity\Database\QueryBuilder\QueryBuilderInterface;
use Entity\Database\QueryBuilder\SelectRequest;
use Entity\Database\QueryBuilder\UpdateRequest;
use Entity\Metadata\Association;
use Entity\Metadata\DataColumn;
use Entity\Metadata\Holder\ProxyFactory;
use Exception;
use function Debug\Tools\dump;

class ModelManager implements ManagerInterface
{
	/**
	 * @var DaoInterface
	 */
	protected DaoInterface $dao;
	protected $modelNamespace;
	protected QueryBuilderInterface $builder;
	protected bool $lazyLoading = true;
	protected string $proxyCachePath = '';

	/**
	 * ModelManager constructor.
	 * @param DaoInterface $dao
	 * @param string $modelNamespace
	 * @throws Exception
	 */
	public function __construct(DaoInterface $dao, string $modelNamespace)
	{
		$this->dao = $dao;
		$this->setClassNamespace($modelNamespace);
		$this->builder = new QueryBuilder($this->dao->getDatasource());

	}

	/**
	 * @param string $path
	 * @return ManagerInterface
	 */
	public function setProxyCachePath(string $path): ManagerInterface
	{
		$this->proxyCachePath = $path;
		return $this;
	}


	public function enableDebug()
	{
		$this->dao->enableDebug();
		return $this;
	}

	/**
	 * @return $this
	 */
	public function disableLazyLoading():ModelManager
	{
		$this->lazyLoading = false;
		return $this;
	}

	/**
	 * @param string $class
	 * @return ModelManager
	 * @throws Exception
	 */
	public function manage(string $class): ModelManager
	{
		if(Model::exists($class)) {
			$dao = new Dao($this->dao->getDatasource());
			$manager = new ModelManager($dao, $class);
			$manager->setProxyCachePath($this->proxyCachePath);
			return $manager;
		} else {
			throw new Exception($class . ' Is not Model can not manage it ');
		}

	}

	/**
	 * @param string $modelNamespace
	 * @return ModelManager
	 */
	public function setClassNamespace(string $modelNamespace): ModelManager
	{
		if(Model::exists($modelNamespace)) {
			$this->modelNamespace = $modelNamespace;
			return $this;
		} else {
			throw new Exception($modelNamespace . ' Is not Model can not manage it ');
		}

	}

	/**
	 * @return QueryBuilderInterface
	 */
	public function builder(): QueryBuilderInterface
	{
		return $this->builder;
	}

	/**
	 * @param $id
	 * @return false|mixed
	 * @throws Exception
	 */
	public function findById($id)
	{
		$request = self::makeFindById($this->modelNamespace, $id);
		$results = $this->dao->execute($request, $this->modelNamespace);
		$model = Model::model($this->modelNamespace);
		$results = $this->processResults($results,$model);
		if (is_array($results)) {
			return $results[0];
		} else {
			return false;
		}
	}

	/**
	 * @param array $params
	 * @return array
	 * @throws Exception
	 */
	public function findBy(array $params)
	{
		$request = self::makeFindBy($this->modelNamespace, $params);
		$results =  $this->dao->execute($request, $this->modelNamespace);
		$model = Model::model($this->modelNamespace);
		if (is_array($results)) {
			return $this->processResults($results,$model);
		} else {
			return [];
		}
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	public function findAll()
	{
		$request = self::makeSelectAll($this->modelNamespace);
		$results = $this->dao->execute($request, $this->modelNamespace);
		$model = Model::model($this->modelNamespace);
		if (is_array($results)) {
			return $this->processResults($results, $model);
		} else {
			return [];
		}
	}

	/**
	 * @param string $associationClassname
	 * @param Model $model
	 * @return array|false|mixed
	 */
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

	}

	/**
	 * @param $model
	 * @param $table
	 * @param $junctionTable
	 * @param $associationTable
	 * @param $associationClassname
	 * @return array|mixed
	 */
	private function findManyToManyToValues($model, $table, $junctionTable, $associationTable, $associationClassname)
	{
		$request = (new SelectRequest($associationTable . '.*', $junctionTable . '.*'))
			->from($associationTable)
			->join($junctionTable, $associationTable. '.id', $associationTable . '_id')
			->where($table . '_id', '=', $model->getId());
		$results = $this->dao->execute($request, $associationClassname);
		return $returns = $results ? $results : [];
	}

	/**
	 * @param $model
	 * @param $table
	 * @param $associationTable
	 * @param $associationClassname
	 * @return array|mixed
	 */
	private function findOneToManyToValues($model, $table, $associationTable, $associationClassname)
	{
		$request = (new SelectRequest($associationTable . '.*'))
			->from($associationTable)
			->where($table . '_id', ' = ', $model->getId());
		$results = $this->dao->execute($request, $associationClassname);
		return $results ? $results : [];
	}

	/**
	 * @param $model
	 * @param $table
	 * @param $associationTable
	 * @param $associationClassname
	 * @return false|mixed
	 */
	private function findManyToOneValues($model, $table, $associationTable, $associationClassname)
	{
		$request = (new SelectRequest(
			$associationTable . '.*')
		)
			->from($associationTable)
			->join($table, $associationTable . '_id', $associationTable . '.id')
			->where($table . '.id', ' = ', $model->getId());
		$results = $this->dao->execute($request, $associationClassname);
		return $results ? $results[0] : false;
	}

	/**
	 * @param $model
	 * @param $associationTable
	 * @param $associationClassname
	 * @param $fk
	 * @return false|mixed
	 */
	private function findOneToOneValues($model, $associationTable, $associationClassname, $fk)
	{
		$request = (new SelectRequest( $associationTable . '.*'))
			->from($associationTable)
			->where($fk, ' = ', $model->getId());
		$results = $this->dao->execute($request, $associationClassname);
		return $results ?: false;
	}

	/**
	 * @param $object
	 * @return mixed
	 */
	public function insert($object)
	{
		$request = self::makeInsert($object);
		$id = $this->dao->execute($request, $this->modelNamespace);
		if ($id) {
			$object->setId($id);
		}
		return $object;
	}

	/**
	 * @param $object
	 * @throws Exception
	 */
	public function create($object)
	{
		throw new Exception('Unimplemented method yet');
		/* $request = self::makeInsert($this->dao->getClassNamespace());
		 $this->dao->execute($request);*/
	}

	/**
	 * @param $object
	 */
	public function update($object)
	{
		$request = self::makeUpdate($object);
		var_dump($request->query());
		$this->dao->execute($request, $this->modelNamespace);
	}

	/**
	 * @param $object
	 */
	public function delete($object)
	{
		$request = self::makeDelete($object);
		$this->dao->execute($request, $this->modelNamespace);
	}

	/**
	 * @param Model $object
	 * @return InsertRequest
	 * @throws \ReflectionException
	 */
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
				if ($associationType == Association::OneToOne || $associationType == Association::ManyToOne) {
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

	/**
	 * @param Model $object
	 * @return UpdateRequest
	 * @throws \ReflectionException
	 */
	protected static function makeUpdate(Model $object): UpdateRequest
	{
		$columns = $object::getColumns();
		$request = new UpdateRequest($object::getTableName());
		$fields = array_keys($columns);
		$associations = $object::getAssociations();
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
		$fields = array_filter($fields, function ($key) {
			if ($key != 'id') {
				return $key;
			}
		}, ARRAY_FILTER_USE_KEY);

		foreach ($columns as $column) {
			$name = $column->getName();
			$type = $column->getType();
			if ($name != 'id') {
				$method = Inflector::camelize('get' . ucfirst($name));
				if ($type == 'fk') {
					$value = $object->$method()->getId();
					$colName = Inflector::tableize($name) . 's_id';
					if ($object->getId() == 0) {
						//$request = self::makeInsert($object);
					}
					//if ID == 0 makeInsert and put value to new  id;
				} else {
					$value = $object->$method();
					$colName = $name;
				}
				$request->set($colName, $value);
			}
		}
		$request->where('id', '=', $object->getId());
		return $request;
	}

	/**
	 * @param Model $object
	 * @return DeleteRequest
	 */
	protected static function makeDelete(Model $object): DeleteRequest
	{
		$request = new DeleteRequest($object::getTableName());
		return $request->where('id', '=', $object->getId());
	}

	/**
	 * @param string $modelNamespace
	 * @return SelectRequest
	 * @throws Exception
	 */
	protected static function makeSelectAll(string $modelNamespace): SelectRequest
	{
		if (Model::exists($modelNamespace)) {
			return (new SelectRequest())->from($modelNamespace::getTableName());
		}
		throw new Exception('Try to build request on non existing model : ' . $modelNamespace);
	}

	/**
	 * @param string $modelNamespace
	 * @param int $id
	 * @return SelectRequest
	 * @throws Exception
	 */
	protected static function makeFindById(string $modelNamespace, int $id): SelectRequest
	{
		if (Model::exists($modelNamespace)) {
			$request = (new SelectRequest())->from($modelNamespace::getTableName());
			return $request->where('id', '=', $id);
		}
		throw new Exception('Try to build request on non existing model : ' . $modelNamespace);

	}

	/**
	 * @param string $modelNamespace
	 * @param array $params
	 * @return SelectRequest
	 * @throws Exception
	 */
	protected static function makeFindBy(string $modelNamespace, array $params): SelectRequest
	{
		if (Model::exists($modelNamespace)) {
			$request = (new SelectRequest())->from($modelNamespace::getTableName());
			foreach ($params as $name => $value) {
				$request->where($name, '=', $value);
			}
			return $request;
		}
		throw new Exception('Try to build request on non existing model : ' . $modelNamespace);
	}

	/**
	 * @param Model $model : proxied object
	 * @param Model $proxy : proxy
	 * @param Association $association : association to lazyload
	 */
	private function lazyLoad(Model $model, Model &$proxy, Association $association)
	{
		$method =  'set' .Inflector::classify($association->getName());
		// set closure with affectation code below
		$loader = new LazyLoader(
			$this,
			'findAssociationValuesBy',
			[$association->getOutClassName(), $model]
		);

		$result = call_user_func_array([$proxy, $method], [$loader]);

	}

	/**
	 * @param array $results
	 * @param Model $model
	 * @return array
	 * @throws Exception
	 */
	protected function processResults(array $results, Model $model): array
	{
		if ($this->lazyLoading) {
			// Todo do not use setters to init proxy but hydrate proxy with an array of loaders
			$proxyFactory = new ProxyFactory();
			$proxyFactory->setCachePath($this->proxyCachePath);
			$loaders = [];
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
					$proxyFactory->addLoader($association->getName(), $this->getLoader($result, $association));
				} else {
					$method =  'set' .Inflector::classify($association->getName());
					$associationValues = $this->findAssociationValuesBy($association->getOutClassName(), $result);
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
			[$association->getOutClassName(), $model]
		);
	}

	public function getModelNamespace()
	{
		return $this->modelNamespace;
	}
}
