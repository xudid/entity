<?php

namespace Entity\Database;
use Exception;
use PDO;

/**
 * Class SelectExecuter
 * @package Entity\Database
 */
class SelectExecuter extends Executer
{
    public function execute()
    {
        parent::execute();
        if (!$this->statmentResult) {
            return false;
        }
        if ($this->className) {
            try {
                return $this->statment->fetchAll(PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, $this->className);
            } catch (Exception $exception) {
                dump($exception);
            }

        } else {
            return $this->statment->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}
