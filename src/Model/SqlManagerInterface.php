<?php


namespace Entity\Model;


interface SqlManagerInterface
{
    public function beginTransation();

    public function commit();

    public function rollback();

}