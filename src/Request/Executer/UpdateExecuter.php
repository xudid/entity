<?php

namespace Xudid\Entity\Request\Executer;

class UpdateExecuter extends Executer
{
    public function execute()
    {
        parent::execute();
        return $this->statmentResult;
    }
}
