<?php


namespace Xudid\Entity\Database\Driver\Mongo;



use Xudid\Entity\Database\Driver\DataSource;
use Xudid\EntityContracts\Database\Driver\DriverInterface;

class MongoDBDataSource extends DataSource
{

    private DriverInterface $driver;

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