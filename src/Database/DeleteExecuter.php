<?php

namespace Entity\Database;

class DeleteExecuter extends Executer
{
    public function execute()
    {
        parent::execute();
        return $this->statmentResult;
    }
}