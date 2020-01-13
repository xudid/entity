<?php


namespace Entity\Database;


interface DriverInterface
{
    public function getConnectionUrl() : string;
    public function getConnection();
}