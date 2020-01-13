<?php


namespace Entity\Database\Mysql;


use Entity\Database\DataSource;
use Entity\Database\DriverInterface;

class MysqlDataSource extends DataSource
{

    /**
     * MysqlDataSource constructor.
     */
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