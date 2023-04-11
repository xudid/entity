<?php

namespace Xudid\Entity\Metadata;

use Xudid\EntityContracts\Metadata\AssociationInterface;

/**
 * Class Association
 */
class Association implements AssociationInterface
{
    const ManyToMany = 'ManyToMany';
    const ManyToOne = 'ManyToOne';
    const OneToMany = 'OneToMany';
    const OneToOne = 'OneToOne';

    public static array $AssociationTypes = [
        self::ManyToMany,
        self::ManyToOne,
        self::OneToMany,
        self::OneToOne,
    ];

    protected string $name;
    protected string $type;
    protected string $fromModel;
    protected string $toModel;
    protected string $table;

    /**
     * Association constructor.
     */
    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function setFromModel(string $fromModel): static
    {
        $this->fromModel = $fromModel;
        return $this;
    }

    public function setToModel(string $toModel): static
    {
        $this->toModel = $toModel;
        return $this;
    }

    public function getFromModel(): string
    {
        return $this->fromModel;
    }

    public function getToModel(): string
    {
        return $this->toModel;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTable(): string
    {
        return '';
    }

    public function setTable(string $table): static
    {
        $this->table = $table;
        return $this;
    }
}
