<?php

namespace Entity\Database;

class InsertExecuter extends Executer
{
    public function execute()
    {
        parent::execute();
        if ($this->statmentResult) {
            return $this->connexion->lastInsertId();
        }
            return false;
    }
}
