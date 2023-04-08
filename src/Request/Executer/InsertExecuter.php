<?php

namespace Xudid\Entity\Request\Executer;

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
