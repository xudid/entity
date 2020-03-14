<?php

namespace Entity\Metadata;;

/**
 * Class Association
 * @package Entity\Metadata
 */
class Association
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
    protected string $holdingClassName;
    protected string $outClassName;

    /**
     * Association constructor.
     * @param string $name
     * @param string $type
     */
    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * @param mixed $holdingClassName
     * @return Association
     */
    public function setHoldingClassName(string $holdingClassName)
    {
        $this->holdingClassName = $holdingClassName;
        return $this;
    }

    /**
     * @param mixed $outClassName
     * @return Association
     */
    public function setOutClassName(string $outClassName)
    {
        $this->outClassName = $outClassName;
        return $this;

    }

    /**
     * @return string
     */
    public function getHoldingClassName(): string
    {
        return $this->holdingClassName;
    }

    /**
     * @return string
     */
    public function getOutClassName(): string
    {
        return $this->outClassName;
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


}