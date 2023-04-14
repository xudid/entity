<?php

namespace Xudid\Entity\Model;

use Core\Security\Password;
use Doctrine\{Inflector\Inflector, Inflector\InflectorFactory, Inflector\Language};
use Xudid\Entity\Attributes\{Column, Id, ManyToMany, ManyToOne, OneToMany, Table};
use Xudid\Entity\Metadata\{Association, DataColumn, ManyAssociation};
use Xudid\EntityContracts\{Metadata\AssociationInterface, Metadata\DataColumnInterface, Model\ModelInterface};
use Exception;
use Psr\Http\Message\ServerRequestInterface;
use Core\Http\Request as HttpRequest;
use ReflectionClass;
use ReflectionException;
use TypeError;

/**
 * Class Model
 * @package Entity\Model
 */
class Model implements ModelInterface
{
    private static Inflector $inflector;
    #[Column('integer')]
    #[Id(true)]
    protected int $id = 0;

    public static function model(string $model): static
    {
        if (self::exists($model)) {
            return new $model();
        } else {
            throw new Exception('Model ' . $model . 'does not exist');
        }
    }

    public static function getClass(): string
    {
        $class = get_class(new static());
        if (str_starts_with($class, 'Proxy')) {
            return get_parent_class(new static([]));
        } else {
            return $class;
        }
    }

    public static function getShortClass(): string
    {
        $s = str_replace('\\', '/', self::getClass());
        $c = explode("/", $s);
        return end($c);
    }

    public static function getTable(): string
    {
        $reflection = new ReflectionClass(static::getClass());
        $attributes = $reflection->getAttributes(Table::class);
        if ($attributes) {
            $attribute = $attributes[0];
            $table = $attribute->newInstance();
            return $table->getName();
        }
        return '';
    }

    public static function getPrimaryKeys(): array
    {
        return array_filter(self::getColumns(), function (DataColumn $column) {
            return $column->isPrimary();
        });
    }

    public static function getForeignKeys(): array
    {
        return [];
    }

    public static function getColumn(string $name): DataColumnInterface
    {
        $columns = self::getColumns();
        foreach ($columns as $column) {
            if ($column->getName() == $name) {
                return $column;
            }
        }

        return $column;
    }

    /**
     * @throws ReflectionException
     */
    public static function getColumns(): array
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
                $columns[$columnName]->setIsPrimary();
                if ($idAttribute->isAutoIncrement()) {
                    $columns[$columnName]->setIsAutoIncrement()->setIsPrimary();
                }
            }
        }
        return $columns;
    }

    /**
     * @throws ReflectionException
     */
    public static function getAssociation(string $className): AssociationInterface
    {
        foreach (self::getAssociations() as $value) {
            if ($value->getToModel() == $className) {
                return $value;
            }
        }

        throw new Exception('Association with ' . $className . 'not found');
    }

    /**
     * @throws ReflectionException
     */
    public function hasAssociation(string $className): bool
    {
        foreach (self::getAssociations() as $association) {
            if ($association->getToModel() == $className) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return Model associations
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
                $outClassname = $manyToManyAttribute->getToModel();
                $association = new ManyAssociation($columnName, Association::ManyToMany);
                $association->setFromModel(static::class);
                $association->setToModel($outClassname);

                $outModel = Model::model($outClassname);
                $association->setTable(self::getTable() . '_' . $outModel::getTable());
                $fromForeignKey = self::getTable() . '_id';
                $toForeignKey = $outModel::getTable() . '_id';
                $association->setFromForeignKey($fromForeignKey);
                $association->setToForeignKey($toForeignKey);
                $associations[$columnName] = $association;
            }

            $manyToOneAttributes = $property->getAttributes(ManyToOne::class);
            foreach ($manyToOneAttributes as $manyToOneAttribute) {
                $manyToOneAttribute = $manyToOneAttribute->newInstance();
                $association = new Association($columnName, Association::ManyToOne);
                $outClassname = $manyToOneAttribute->getOutClassname();
                $association->setFromModel(static::class);
                $association->setToModel($outClassname);
                $outModel = self::model($outClassname);
                $association->setTable(self::getTable() . '_' . $outModel::getTable());
                $associations[$columnName] = $association;
            }

            $oneToManyAttributes = $property->getAttributes(OneToMany::class);
            foreach ($oneToManyAttributes as $oneToManyAttribute) {
                $oneToManyAttribute = $oneToManyAttribute->newInstance();
                $association = new Association($columnName, Association::OneToMany);
                $association->setFromModel(static::class);
                $association->setToModel($oneToManyAttribute->getOutClassname());
                $associations[$columnName] = $association;
            }

            $oneToOneAttributes = $property->getAttributes(OneToMany::class);
            foreach ($oneToOneAttributes as $oneToOneAttribute) {
                $oneToOneAttribute = $oneToOneAttribute->newInstance();
                $association = new Association($columnName, Association::OneToMany);
                $association->setFromModel(static::class);
                $association->setToModel($oneToOneAttribute->getOutClassname());
                $associations[$columnName] = $association;
            }
        }
        return $associations;
    }

    public function getPropertyValue(string $propertyName): mixed
    {
        $method = 'get' . ucfirst($propertyName);
        if (method_exists(self::getClass(), $method)) {
            if ($this->isProxy()) {
                return $this->$propertyName;
            } else {
                return $this->$method();
            }
        }

        return null;
    }

    public function isProxy(): bool
    {
        return property_exists(static::getClass(), 'wrapped');
    }

    public static function getGetters(): array
    {
        return static::getMethodsWithPrefix('get');
    }

    public static function getSetters(): array
    {
        return static::getMethodsWithPrefix('set');
    }

    private static function getMethodsWithPrefix(string $prefix): array
    {
            $methods = get_class_methods(static::class);
        $methods = array_filter($methods, function ($method) use ($prefix) {
            if (str_starts_with($method, $prefix)) {
                return $method;
            }
            return false;
        });

        return $methods;
    }

    public static function hasGetter($getter): bool
    {
        $getters = static::getGetters();

        return in_array($getter, $getters);
    }

    public static function hasSetter($setter): bool
    {
        $setters = static::getSetters();

        return in_array($setter, $setters);
    }

    public function __construct(array $datas = [])
    {
        //return self::hydrate($datas);
    }

    public static function hydrate(array $datas): static
    {
        $object = new static($datas);
        $setters = $object::getSetters();
        foreach ($datas as $key => $data) {
            $setter = 'set' . static::inflector()->classify($key);
            if (in_array($setter, $setters)) {
                try {
                    $object->$setter($data);
                } catch (TypeError $error) {
                }
            }
        }
        return $object;
    }

    public static function exists(string $className): bool
    {
        if (class_exists($className) && Model::class == get_parent_class($className)) {
            return true;
        }
        return false;
    }

    public function handle(ServerRequestInterface $request, $prefix = '')
    {
        $prefix = strlen($prefix) > 0 ? $prefix : static::getTable();
        $fields = static::getColumns();
        foreach ($fields as $field) {
            $baseFieldName = $field->getName();
            $fieldName = $prefix . '_' . $baseFieldName;
            if (HttpRequest::has($request, $fieldName)) {
                $value = HttpRequest::get($request, $fieldName);
                if ($field->getType() == 'password' && $value) {
                    $value = Password::hash($value);
                }
                $method = 'set' . ucfirst($baseFieldName);
                if(static::hasSetter($method)) {
                    $value = htmlentities($value, ENT_QUOTES, 'UTF-8');
                   call_user_func([$this, $method], $value);
                }
            }
        }
    }

    public function __get($key)
    {
        if (is_callable($this->$key)) {
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
        $name = static::inflector()->classify($name);
        $setter = 'set' . $name;
        if (in_array($setter, get_class_methods($this))) {
            $this->$setter($value);
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId($id): static
    {
        if (is_numeric($id)) {
            $this->id = (int)$id;
        }
        return $this;
    }

    private static function inflector(): Inflector
    {
        if (static::$inflector == null) {
            static::$inflector = InflectorFactory::createForLanguage(Language::ENGLISH)->build();
        }
        return static::$inflector;
    }
}
