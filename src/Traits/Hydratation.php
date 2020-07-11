<?php

namespace Entity\Traits;

trait Hydratation
{
    public static function hydrate(array $datas)
    {
        $object = new static();

        $methods = get_class_methods(__CLASS__);
        $keys = array_keys($datas);
        foreach ($keys as $key) {
            $key = strtolower($key);
            $setter = 'set'.ucfirst($key);
            $value = $datas[$key];
            if (in_array($setter, $methods)) {
                $object->$setter($value);
            }
        }
        return $object;
    }
}
