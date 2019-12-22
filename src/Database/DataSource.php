<?php


namespace Ui\Model\Database;


interface DataSourceInterface
{
    public function getName(): string;
    public function getConfig(): array;
}
