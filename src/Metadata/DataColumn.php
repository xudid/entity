<?php

namespace Xudid\Entity\Metadata;

use Xudid\EntityContracts\Metadata\DataColumnInterface;

class DataColumn implements DataColumnInterface
{
    private string $name;
    private string $type;
    private bool $isPrimary = false;
    private bool $isAutoIncrement = false;
    /**
     * @var true
     */
    private bool $isUnique = false;
    /**
     * @var true
     */
    private bool $IsNull = false;
    /**
     * @var null
     */
    private $default = null;

    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setIsPrimary(): static
    {
        $this->isPrimary = true;
        return $this;
    }

    public function setIsAutoIncrement(): static
    {
        $this->isAutoIncrement = true;
        return $this;
    }

    public function isPrimary(): bool
    {
        return $this->isPrimary;
    }

    public function isAutoIncrement(): bool
    {
        return $this->isAutoIncrement;
    }

    public function unique(): static
    {
        $this->isUnique = true;
        return $this;
    }

    public function isUnique(): bool
    {
        return $this->isUnique;
    }

    public function null(): static
    {
        $this->IsNull = true;
        return $this;
    }

    public function isNull(): bool
    {
        return $this->IsNull;
    }

    public function default(): static
    {
        $this->default = null;
        return $this;
    }

    public function getDefault(): mixed
    {
        return $this->default;
    }
}
