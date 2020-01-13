<?php


namespace Entity\Database;


interface DataSourceInterface
{
    public function getName(): string;
    public function getConfig(): array;
}
