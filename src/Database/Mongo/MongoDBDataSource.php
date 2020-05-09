<?php


namespace Entity\Database\Mongo;


use Entity\Database\DataSource;
use Entity\Database\DriverInterface;

class MongoDBDataSource extends DataSource
{

    public function __construct(string $name, array $config)
    {
        parent::__construct($name,$config);
        $this->driver = new MongoDBDriver($this);
    }
    /**
     * @inheritDoc
     */
    public function getDriver(): DriverInterface
    {
        return $this->driver;
    }
}