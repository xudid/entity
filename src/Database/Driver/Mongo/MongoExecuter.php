<?php


namespace Xudid\Entity\Database\Driver\Mongo;

use Xudid\EntityContracts\Database\Driver\DataSourceInterface;

class MongoExecuter
{
    private $driver;

    public function __construct(DataSourceInterface $dataSource, string $class)
    {
        $this->driver = $dataSource->getDriver();
        $this->class = $class;
    }

    public function execute($request)
    {
        $manager = $this->driver->getConnection();
        $dbname = '';
        $c_users = '';
        $read = '';
        $cursor = $manager->executeQuery("$dbname.$c_users", $read);
        return  json_encode($cursor->toArray());
    }
}