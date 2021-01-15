<?php

namespace Entity\Database;

class UpdateExecuter extends Executer
{
    public function execute()
    {
        parent::execute();
        return $this->statmentResult;
    }
}