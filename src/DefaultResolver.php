<?php


namespace Entity;
use InvalidArgumentException;
use Ui\Views\ViewFieldsDefinition;
use Ui\Views\ViewFieldsDefinitionInterface;
use Ui\Views\ViewFilterInterface;
use Ui\Views\ViewFilter;

class DefaultResolver
{
    public static function resolv(string $typeToResolv, string $subpackage, string $entiyClassName, bool $withNamespace = true): string
    {
        if (!empty($entiyClassName) && !empty($subpackage)) {
            $path_parts = explode('\\', $entiyClassName);
            $classname = array_pop($path_parts) . $typeToResolv;
            if ($withNamespace) {
                $parts_count = count($path_parts);
                if ($parts_count <= 1) {
                    $path_parts[] = $subpackage;
                } else {
                    $path_parts[$parts_count - 1] = $subpackage;
                }
                $classname = implode('\\', [implode('\\', $path_parts), $classname]);
            }
            return $classname;
        } else {
            throw new InvalidArgumentException();
        }
    }

    public static function getEntityClassName(string $classname, bool $withNamespace = true)
    {
        try {
            /** @var string $fieldDefinitionName */
            $fieldDefinitionName = self::resolv("Entity", "Entities", $classname, $withNamespace);
            return $fieldDefinitionName;
        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException("DefaultEntityResolver can't resolve 
                                                                Entity without entity class name");
        }
    }

    /**
     * @param string $classname
     * @return array
     * @throws \Exception
     */
    public static function getFieldDefinitions(string $classname, bool $withNamespace = true) :ViewFieldsDefinitionInterface
    {
        try {
            /** @var string $fieldDefinitionName */
            $fieldDefinitionName = self::resolv("FieldDefinition", "Views", $classname, $withNamespace);
            if (class_exists($fieldDefinitionName)) {
                $definitions = new $fieldDefinitionName();
                if ($definitions instanceof ViewFieldsDefinition) {
                    return $definitions;
                } else {
                    throw new \Exception("fields definition class : $fieldDefinitionName is not a subclass of ViewFieldsDefinition");
                }
            } else {
                throw new \Exception("fields definition class $fieldDefinitionName for $classname doesn't exist ");
            }

        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException("DefaultFieldDefinitionResolver can't resolve 
                                                                FormFilter without entity class name");
        }
    }
    public static function getFilter(string $classname,bool $withNamespace = true) : ViewFilterInterface
    {
        try {
            /** @var string $fieldDefinitionName */
            $fieldDefinitionName = self::resolv("Filter","Views", $classname, $withNamespace);
            if (class_exists($fieldDefinitionName)) {
                $filter = new $fieldDefinitionName();
                if ($filter instanceof ViewFilter) {
                    return $filter;
                } else {
                    throw new \Exception("fields filter class : $fieldDefinitionName does not a subclass of ViewFilterInterface");
                }
            } else {
                throw new \Exception("fields filter class for $classname doesn't exist");
            }

        } catch (InvalidArgumentException $e) {
            throw new InvalidArgumentException("DefaultFormFilterResolver can't resolve 
                                                    FormFilter without entity class name");
        }
    }
}