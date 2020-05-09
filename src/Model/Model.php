<?php

namespace Entity\Model;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Entity\Metadata\AnnotationParser;
use Entity\Metadata\Association;
use Entity\Metadata\DataColumn;
use Entity\Metadata\ManyAssociation;
use Entity\Traits\Hydratation;
use ReflectionClass;

/**
 * Class Model
 * @package Entity\Model
 */
class Model
{
    use Hydratation;
    /**
     * @var int $id
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    protected int $id = 0;
    public static function getClass()
    {
        $class = get_class(new static([]));
        if (strpos($class, 'Proxy')) {
            return get_parent_class(new static([]));
        } else {
            return $class;
        }

    }

    public static function getShortClass()
    {
        $s = str_replace('\\', '/', self::getClass([]));
        $c = explode("/", $s);
        return end($c);
    }
    public static function getTableName() : string
    {
        $class = self::getClass();
        $comment = (new ReflectionClass($class))->getDocComment();
        preg_match('#@Table\(name="([\w_]*)"#',$comment,$matches);
        return $matches[1];
    }

    public static function getPrimaryKeys() : array
    {
        return array_filter(self::getColumns(), function(DataColumn $column){
            return $column->isPrimary();
        });
    }

    public static function getForeignKeys() : array
    {
        return [];
    }

    public static function getColumns() : array
    {
        $columns = [];
        $properties = (new ReflectionClass(self::getClass()))->getProperties();
        foreach ($properties as $property) {
            $columnName = $property->getName();
            $columnComment  = $property->getDocComment();
            preg_match('#@Column\(type="([\w_]*)"\)#', $columnComment, $matches);
            if($matches) {
                $columns[$columnName] = new DataColumn($columnName, $matches[1]);
            }
            preg_match('#@Id#', $columnComment, $matches);
            if($matches) {
                $columns[$columnName]->setIsPrimary();
            }
            preg_match('#@GeneratedValue#', $columnComment, $matches);
            if($matches) {
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
        $assocation = array_filter($assocations, function (Association $value) use($className){
            if ($value->getOutClassName() == $className) {
                return true;
            }
        });
        return $assocation ?: false;
    }

    public static function getAssociations()
    {
        $associations = [];
        $class = self::getClass();
        $properties = (new ReflectionClass($class))->getProperties();
        foreach ($properties as $property) {
            $columnName = $property->getName();
            $columnComment  = $property->getDocComment();
            $attributeString = '';
            $associationType = '';
            foreach (Association::$AssociationTypes as $type ) {
                $attributeString = AnnotationParser::extractAttributeString($type, $columnComment);
               if (strlen($attributeString)) {
                   $associationType = $type;
                   break;
               }
            }
            if (strlen($attributeString)) {
                $attributes = AnnotationParser::parseAttributes($attributeString);
                if($attributes) {
                    if($associationType == 'ManyToMany' || $associationType == 'OneToMany') {
                        $association = new ManyAssociation($columnName, $associationType);
                    } else {
                        $association = new Association($columnName, $associationType);
                    }
                    if ($attributeString) {
                        if (array_key_exists('targetEntity', $attributes)) {
                            $association->setOutClassName($attributes['targetEntity']);
                        }
                        $associations[$columnName] = $association->setHoldingClassName(static::class);
                        if ($association instanceof ManyAssociation && $association->getOutClassName()) {

                            $outClassName = $association->getOutClassName();
                            try {
                                $outClassRelfection = new ReflectionClass($outClassName);
                                $outClass = $outClassRelfection->newInstance([]);
                                $association->setTableName(self::getTableName() . '_' . $outClass::getTableName());
                            } catch (\Exception $exception) {
                            }
                        }
                    }
                }
            }
        }
        return $associations;
    }

    public function getPropertyValue(string $propertyName)
    {
        $this->isProxy();
        $method = 'get' . ucfirst($propertyName);
        $class = self::getClass();
        try {
            $reflectionClass = new ReflectionClass($class);
            if($reflectionClass->hasMethod($method)) {
                if ($this->isProxy()) {
                    return $this->$propertyName;
                } else {
                    return $this->$method();
                }

            }
        } catch (\ReflectionException $e) {
            dump($e);
        }

    }
    public function isProxy()
    {
        $reflectionClass = new ReflectionClass($this);
        return $reflectionClass->hasProperty('wrapped');
    }


    public static function getGetters()
    {
        $columns = self::getColumns();
        $methods =[];
        foreach ($columns as $column)
        {
            $method[] = 'get'  . ucfirst($column->getName());
        }
        return $methods;
    }

    public static function getSetters()
    {
        $columns = self::getColumns();
        $methods =[];
        foreach ($columns as $column)
        {
            $method[] = 'set'  . ucfirst($column->getName());
        }
        return $methods;
    }

    public function __construct(array $datas)
    {

    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }
}
