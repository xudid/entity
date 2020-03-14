<?php


namespace Entity\Database;


interface DriverInterface
{
    public function __construct(DataSourceInterface $dataSource);
    public function getConnectionUrl() : string;
    public function getConnection();
}