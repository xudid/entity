<?php


namespace Entity\Database\Mongo;


use Entity\Database\DataSourceInterface;

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
        $cursor = $manager->executeQuery("$dbname.$c_users", $read);
        return  json_encode($cursor->toArray());
    }
}