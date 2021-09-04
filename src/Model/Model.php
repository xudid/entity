<?php

namespace Entity\Model;

use Doctrine\Common\Inflector\Inflector;
use Entity\Database\Attributes\Column;
use Entity\Database\Attributes\Id;
use Entity\Database\Attributes\OneToMany;
use Entity\Database\Attributes\ManyToMany;
use Entity\Database\Attributes\Table;
use Entity\Metadata\AnnotationParser;
use Entity\Metadata\Association;
use Entity\Metadata\DataColumn;
use Entity\Metadata\ManyAssociation;
use Exception;
use ReflectionClass;
use ReflectionException;
use TypeError;

/**
 * Class Model
 * @package Entity\Model
 */
class Model
{
	//use Hydratation;

	/**
	 * @var int $id
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 */
	#[Column('integer')]
	#[Id(true)]
	protected int $id = 0;

    public static function model(string $model) : Model
    {
        if (self::exists($model)) {
            return new $model();
        } else {
			throw new Exception('Model ' . $model . 'does not exist');
		}
    }

	/**
	 * @return false|string
	 */
	public static function getClass()
	{
		$class = get_class(new static());
		if (strpos($class, 'Proxy')) {
			return get_parent_class(new static([]));
		} else {
			return $class;
		}

	}

	/**
	 * @return mixed|string
	 */
	public static function getShortClass()
	{
		$s = str_replace('\\', '/', self::getClass());
		$c = explode("/", $s);
		return end($c);
	}

	/**
	 * @return string
	 */
	public static function getTableName(): string
	{

		try {
			$class = self::getClass();
			$comment = (new ReflectionClass($class))->getDocComment();
			preg_match('#@Table\(name="([\w_]*)"#', $comment, $matches);
			return $matches[1];
		} catch (ReflectionException $e) {
			dump($e);
		}
		return '';
	}

	public static function getTable()
	{
		$reflection = new ReflectionClass(static::getClass());
		$attributes = $reflection->getAttributes(Table::class);
		if ($attributes) {
			$attribute = $attributes[0];
			$table = $attribute->newInstance();
			return $table->getName();
		}
	}

	/**
	 * @return array
	 */
	public static function getPrimaryKeys(): array
	{
		return array_filter(self::getColumns(), function (DataColumn $column) {
			return $column->isPrimary();
		});
	}

	/**
	 * @return array
	 */
	public static function getForeignKeys(): array
	{
		return [];
	}

	/**
	 * @param string $name
	 * @return DataColumn|null
	 */
	public static function getColumn(string $name) : ?DataColumn
	{
		$columns = self::getColumns();
		$column = null;
		foreach ($columns as $col) {
			$column = $col->getName() == $name ?: null;
			if ($col->getName() == $name) {
				$column = $col;
				break;
			}
		}
		return $column;
	}

	/**
	 * @deprecated
	 * @return array
	 * @throws ReflectionException
	 */
	public static function getTableColumns(): array
	{
		$columns = [];
		$properties = (new ReflectionClass(self::getClass()))->getProperties();
		foreach ($properties as $property) {
			$columnName = $property->getName();
			$columnComment = $property->getDocComment();
			preg_match('#@Column\(type="([\w_]*)"\)#', $columnComment, $matches);
			if ($matches) {
				// dump($columnName, $matches[1]);
				$columns[$columnName] = new DataColumn($columnName, $matches[1]);
			}
			preg_match('#@Id#', $columnComment, $matches);
			if ($matches) {
				$columns[$columnName]->setIsPrimary();
			}
			preg_match('#@GeneratedValue#', $columnComment, $matches);
			if ($matches) {
				$columns[$columnName]->setIsAutoIncrement();
			}
		}
		return $columns;

	}

	/**
	 * @return array
	 * @throws ReflectionException
	 */
	public static function getColumns()
	{
		$columns = [];
		$properties = (new ReflectionClass(self::getClass()))->getProperties();
		foreach ($properties as $property) {
			$columnName = $property->getName();
			$columnAttributes = $property->getAttributes(Column::class);
			if ($columnAttributes) {
				$columnAttribute = $columnAttributes[0];
				$columnAttribute = $columnAttribute->newInstance();
				$columns[$columnName] = new DataColumn(
					$columnName,
					$columnAttribute->getType()
				);
			}
			$idsAttributes = $property->getAttributes(Id::class);
			if ($idsAttributes) {
				$idAttribute = $idsAttributes[0];
				$idAttribute = $idAttribute->newInstance();
				$columns[$columnName]->setIsAutoIncrement();
			}
		}
		return $columns;
	}

	/**
	 * @param string $className
	 * @return Association|false
	 */
	public static function getAssociation(string $className)
	{
		$assocations = self::getAssociations();
		$association = false;
		foreach ($assocations as $value) {
			if ($value->getOutClassName() == $className) {
				$association = $value;
				break;
			}
		}
		return $association;
	}

	/**
	 * Return Model association
	 * @return array
	 * @throws ReflectionException
	 */
	public static function getAssociations(): array
	{
		$associations = [];
		$class = self::getClass();
		$properties = (new ReflectionClass($class))->getProperties();
		foreach ($properties as $property) {
			$columnName = $property->getName();
			$manyToManyAttributes = $property->getAttributes(ManyToMany::class);
			foreach ($manyToManyAttributes as $manyToManyAttribute) {
				$manyToManyAttribute = $manyToManyAttribute->newInstance();
				$association = new ManyAssociation($columnName, Association::ManyToMany);
				$outClassname = $manyToManyAttribute->getOutClassname();
				$association->setOutClassName($outClassname);
				$association->setHoldingClassName(static::class);
				$outModel = self::model($outClassname);
				$association->setTableName(self::getTableName() . '_' . $outModel::getTableName());
				$associations[$columnName] = $association;
			}

			$manyToOneAttributes = $property->getAttributes(ManyToOne::class);
			foreach ($manyToOneAttributes as $manyToOneAttribute) {
				$manyToOneAttribute = $manyToOneAttribute->newInstance();
				$association = new Association($columnName, Association::ManyToOne);
				$outClassname = $manyToOneAttribute->getOutClassname();
				$association->setOutClassName($outClassname);
				$association->setHoldingClassName(static::class);
				/*$outModel = self::model($outClassname);
				$association->setTableName(self::getTableName() . '_' . $outModel::getTableName());*/
				$associations[$columnName] = $association;
			}

			$oneToManyAttributes = $property->getAttributes(OneToMany::class);
			foreach ($oneToManyAttributes as $oneToManyAttribute) {
				$oneToManyAttribute = $oneToManyAttribute->newInstance();
				$association = new Association($columnName, Association::OneToMany);
				$association->setOutClassName($oneToManyAttribute->getOutClassname());
				$association->setHoldingClassName(static::class);
				$associations[$columnName] = $association;
			}

			$oneToOneAttributes = $property->getAttributes(OneToMany::class);
			foreach ($oneToOneAttributes as $oneToOneAttribute) {
				$oneToOneAttribute = $oneToOneAttribute->newInstance();
				$association = new Association($columnName, Association::OneToMany);
				$association->setOutClassName($oneToOneAttribute->getOutClassname());
				$association->setHoldingClassName(static::class);
				$associations[$columnName] = $association;
			}
		}
		return $associations;
	}

	/**
	 * @deprecated
	 * @return array
	 * @throws Exception
	 */
	public static function getTableAssociations()
	{
		$associations = [];
		$class = self::getClass();
		try {
			$properties = (new ReflectionClass($class))->getProperties();
			foreach ($properties as $property) {
				$columnName = $property->getName();
				$columnComment = $property->getDocComment();
				$association = null;
				foreach (Association::$AssociationTypes as $type) {
					$attributeString = AnnotationParser::extractAttributeString($type, $columnComment);
					if (strlen($attributeString)) {
						$associationType = $type;
						if ($associationType == 'ManyToMany') {
							$association = new ManyAssociation($columnName, $associationType);
						} else {
							$association = new Association($columnName, $associationType);
						}
						$attributes = AnnotationParser::parseAttributes($attributeString);
						if ($attributes && array_key_exists('targetEntity', $attributes)) {
							$association->setOutClassName($attributes['targetEntity']);
							$association->setHoldingClassName(static::class);
						} else {
							dump('missing attributes', $attributeString, $attributes);
							throw new Exception("Missing or Invalid association for $columnName : " . $attributeString);
						}
						break;
					}
				}
				if ($association) {
					$associations[$columnName] = $association;
					if ($association instanceof ManyAssociation && $association->getOutClassName()) {
						$outClassName = $association->getOutClassName();
						try {
							$outClassRelfection = new ReflectionClass($outClassName);
							$outClass = $outClassRelfection->newInstance([]);
							$association->setTableName(self::getTableName() . '_' . $outClass::getTableName());
						} catch (\Exception $exception) {
							dump($exception);
						}
					}
				}
			}
			return $associations;
		} catch (ReflectionException $e) {
		}
	}

	/**
	 * @param string $propertyName
	 * @return |null
	 */
	public function getPropertyValue(string $propertyName)
	{
		$this->isProxy();
		$method = 'get' . ucfirst($propertyName);
		$class = self::getClass();
		try {
			$reflectionClass = new ReflectionClass($class);
			if ($reflectionClass->hasMethod($method)) {
				if ($this->isProxy()) {
					return $this->$propertyName;
				} else {
					return $this->$method();
				}

			}
		} catch (ReflectionException $e) {
			dump($e);
		}
		return null;
	}

	/**
	 * @return bool
	 */
	public function isProxy() : bool
	{
		try {
			$reflectionClass = new ReflectionClass($this);
			return $reflectionClass->hasProperty('wrapped');
		} catch (ReflectionException $e) {
			dump($e);
		}
		return false;
	}

	/**
	 * @return array
	 */
	public static function getGetters()
	{
		$methods =  get_class_methods(static::class);
		$methods = array_filter($methods, function($method){
			if (substr($method, 0, 3) == 'get') {
				return $method;
			}
		});
		return $methods;
	}

	/**
	 * @return array
	 */
	public static function getSetters()
	{
		$methods =  get_class_methods(static::class);
		$methods = array_filter($methods, function($method){
			if (substr($method, 0, 3) == 'set') {
				return $method;
			}
		});
		return $methods;
	}

	public function __construct(array $datas = [])
	{
		//return self::hydrate($datas);
	}

	public static function hydrate(array $datas)
	{
		$object = new static($datas);
		$setters = $object::getSetters();
		foreach ($datas as $key => $data) {
			$setter = 'set' . Inflector::classify($key);
			if (in_array($setter, $setters)) {
				try {
					$object->$setter($data);
				} catch (TypeError $error) {
					dump($error);
				}
			}
		}
		return $object;
	}

	/**
	 * @param string $className
	 * @return bool
	 */
	public static function exists(string $className) : bool
	{
		if (class_exists($className) && Model::class == get_parent_class($className)) {
			return true;
		}
		return false;
	}

	public function __get($key)
	{
		if (is_callable($this->$key))
		{
			$this->$key = call_user_func($this->$key);
		}

		return $this->$key;
	}

	/**
	 * Magic setter to allow PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE
	 * to set table_ized column names
	 * @param $name
	 * @param $value
	 */
	public function __set($name, $value)
	{
		$name = Inflector::classify($name);
		$setter = 'set' . $name;
		if (in_array($setter, get_class_methods($this))) {
			$this->$setter($value);
		}
	}

	/**
	 * @return int
	 */
	public function getId(): int
	{
		return $this->id;
	}

	/**
	 * @param $id
	 * @return $this
	 */
	public function setId($id) {
		if (is_numeric($id)) {
			$this->id = (int)$id;
		}
		return $this;
	}
}
