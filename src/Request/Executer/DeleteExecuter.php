<?php

namespace Xudid\Entity\Request\Executer;

class DeleteExecuter extends Executer
{
    public function execute()
    {
        parent::execute();
        return $this->statmentResult;
    }
}