<?php

namespace Entity\Model;

use Entity\Database\QueryBuilder\QueryBuilderInterface;

interface ManagerInterface
{
    public function enableDebug();

    public function builder(): QueryBuilderInterface;

    public function findById($id);

    public function findBy(array $params);

    public function findAll();

    public function findAssociationValuesBy(string $associationClassname, Model $model);

    public function insert($object);

    public function create($object);

    public function update($object);

    public function delete($object);
}
