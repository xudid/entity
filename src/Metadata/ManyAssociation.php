<?php


namespace Entity\Metadata;


class ManyAssociation extends Association
{
    private string $tableName;

    /**
     * ManyAssociation constructor.
     */
    public function __construct(string $name, string $type)
    {
        parent::__construct($name, $type);
    }


    public function getTableName()
    {
        return $this->tableName;
    }

    public function setTableName(string $tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }
}