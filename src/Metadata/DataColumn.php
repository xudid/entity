<?php


namespace Entity\Metadata;

/**
 * Class DataColumn
 * @package Entity\Metadata
 */
class DataColumn
{
    /**
     * @var string
     */
    private string $name;
    /**
     * @var string
     */
    private string $type;
    /**
     * @var bool
     */
    private bool $isPrimary = false;
    /**
     * @var bool
     */
    private bool $isAutoIncrement = false;

    /**
     * DataColumn constructor.
     * @param string $name
     * @param string $type
     */
    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    public function setIsPrimary()
    {
        $this->isPrimary = true;
        return $this;
    }

    public function setIsAutoIncrement()
    {
        $this->isAutoIncrement = true;
        return $this;
    }

    /**
     * @return bool
     */
    public function isPrimary(): bool
    {
        return $this->isPrimary;
    }

    /**
     * @return bool
     */
    public function isAutoIncrement(): bool
    {
        return $this->isAutoIncrement;
    }
}