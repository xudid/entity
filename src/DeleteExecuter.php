<?php

namespace Entity;
use Entity\Database\Executer;

class DeleteExecuter extends Executer
{
    public function execute()
    {
        parent::execute();
        return $this->statmentResult;
    }
}