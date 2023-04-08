<?php

namespace Xudid\Entity\Database\Driver;

use Xudid\EntityContracts\Database\Driver\DataSourceInterface;
use Xudid\EntityContracts\Database\Driver\DriverInterface;

abstract class DataSource implements DataSourceInterface
{
    private  string $name = "";
    private array $config = [];

    public function __construct(string $name, array $config)
    {
        $this->name = $name;
        $this->config = $config;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public abstract function getDriver() : DriverInterface;
}
