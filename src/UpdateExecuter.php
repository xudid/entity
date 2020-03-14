<?php

namespace Entity;
use Entity\Database\Executer;

class UpdateExecuter extends Executer
{
    public function execute()
    {
        parent::execute();
        return $this->statmentResult;
    }
}