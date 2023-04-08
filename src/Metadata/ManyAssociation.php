<?php

namespace Xudid\Entity\Metadata;

class ManyAssociation extends Association
{
    private string $table;
    private string $fromForeignKey;
    private string $toForeignKey;

    public function __construct(string $name, string $type)
    {
        parent::__construct($name, $type);
    }

    public function getTable()
    {
        return $this->table;
    }

    public function setTable(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    public function setFromForeignKey(string $foreignKey): static
    {
        $this->fromForeignKey = $foreignKey;
        return $this;
    }
    
    public function setToForeignKey(string $foreignKey): static
    {
        $this->toForeignKey = $foreignKey;
        return $this;
    }
}
