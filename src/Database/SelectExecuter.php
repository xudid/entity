<?php

namespace Entity\Database;
use PDO;

class SelectExecuter extends Executer
{
    public function execute()
    {
        parent::execute();
        if ($this->statmentResult) {
            return $this->statment->fetchAll(PDO::FETCH_CLASS, $this->className);
        }
        return false;
    }
}
