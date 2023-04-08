<?php

namespace Xudid\Entity\Database\Driver\Mysql;

use Xudid\Entity\Database\Driver\DataSource;
use Xudid\EntityContracts\Database\Driver\DriverInterface;

class MysqlDataSource extends DataSource
{
    private MysqlDriver $driver;

    public function __construct(string $name, array $config)
    {
        parent::__construct($name,$config);
        $this->driver = new MysqlDriver($this);
    }

    public function getDriver() : DriverInterface
    {
        return $this->driver;
    }
}