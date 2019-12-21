<?php

namespace Brick\Db;

/**
 * Maintains a list of object affected by a business transaction
 * and coordinates the writing out of changes and the resolution
 * of concurrency problems
 */
class UnitOfWork
{
    private $identyMap = null;
    private $newObjects = [];
    private $dirtyObjects = [];
    private $cleanObjects = [];
    private $removedObjects = [];
    private $mappers = [];


    /*
    *
    *
    */

    function __construct()
    {
        $this->identyMap = new \Brick\Db\IdentityMap();

    }

    public function load()
    {

    }


    public function registerNew($object)
    {
        $id = $object->getId();
        $rc = new \ReflectionClass($object);
        $classname = $rc->getName();
        if (!\in_array($classname, $this->mappers)) {
            $this->mappers[$classname] = new Mapper($classname);
        }

        if (!\is_null($id)) {
            if (!\in_array($id, $this->dirtyObjects[$classname]) &&
                !\in_array($id, $this->removedObjects[$classname]) &&
                !\in_array($id, $this->newObjects[$classname])) {
                $this->newObjects[$classname] = $id;
            }
        }
    }

    public function registerClean()
    {

    }


    /*
    * to use after an object modification
    */
    public function registerDirty($object)
    {
        $id = $object->getId();
        $rc = new \ReflectionClass($object);
        $classname = $rc->getName();
        if (!\is_null($id)) {
            if (!\in_array($id, $this->removedObjects[$classname]) &&
                !\in_array($id, $this->dirtyObjects[$classname]) &&
                !\in_array($id, $this->newObjects[$classname])) {
                $this->dirtyObjects[$classname] = $id;
            }
        }
    }

    public function registerDeleted()
    {

    }

    public function commit()
    {
        $this->insertNew();
        $this->updateDirty();
        $this->deleteRemoved();
    }

    private function insertNew()
    {
        foreach ($this->newObjects as $key => $value) {
            foreach ($this->newObjects[$key] as $id) {
                $obj = $this->newObjects[$key][$id];
            }
        }
    }

    private function updateDirty()
    {

    }

    private function deleteRemoved()
    {

    }
}
