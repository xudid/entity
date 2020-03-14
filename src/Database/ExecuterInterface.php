<?php


namespace Entity\Database;


use Entity\Database\QueryBuilder\Request;

interface ExecuterInterface
{
    public function __construct(DriverInterface $driver);
    public function className(string $className);
    public function request(Request $request);
    public function execute();
    public function enableDebug();

}