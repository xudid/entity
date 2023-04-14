<?php

namespace Xudid\Entity\Database\Driver\Mysql;

use mysqli;
use mysqli_stmt;
use Xudid\EntityContracts\Database\Driver\DriverInterface;
use Xudid\EntityContracts\Database\Driver\DriverException;

class MysqliDriver implements DriverInterface
{
    const FETCH_ASSOC = MYSQLI_ASSOC;
    const FETCH_CLASS = 2;
    private mysqli $mysqli;
    private mysqli_stmt $currentStatment;
    private $fetchMode = self::FETCH_ASSOC;
    private $className = '';

    public function __construct(
        private string  $host,
        private string  $port,
        private string  $database,
        private string  $username,
        private string  $password
    ) {
        $this->mysqli = new mysqli($this->host, $this->username, $this->password, $this->database, $this->port);
    }

    public function query(string $request)
    {
        $result = $this->mysqli->query($request);
        if ($result === false) {
            throw new DriverException();
        }

        if ($result === true) {
            return $this->mysqli->affected_rows;
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function bind($request)
    {
        $bindings = [
            'ok' => [],
            'error' => []
        ];
        $this->currentStatment = $this->mysqli->prepare($request->toPreparedSql());
        foreach ($request->getBindings() as $param => $value) {
            $binded = $this->currentStatment->bind_param($param, $value);
            if (!$binded) {
                $bindings['error'][$param] = false;
            } else {
                $bindings['ok'][$param] = $value;
            }
        }

        return $bindings;
    }

    public function fetch(): mixed
    {
        if (!$this->fetchResult) {
            $this->currentStatment->execute();
            $this->fetchResult = $this->currentStatment->get_result();
        }

        if ($this->fetchMode == self::FETCH_CLASS) {
            $return = $this->fetchResult->fetch_object($this->className);
        } else {
            $return = $this->fetchResult->fetch_assoc();
        }

        if ($return === false) {
            throw new DriverException();
        }

        return $return;
    }

    public function fetchAll(): array
    {
        $this->currentStatment->execute();
        if ($this->fetchMode == self::FETCH_CLASS) {
            $queryResult = $this->currentStatment->get_result();
            $results = [];
            while ($result = $queryResult->fetch_object($this->className)) {
                $results = $result;
            }
            return $results;
        } else {
            return  $this->currentStatment->get_result()->fetch_all(MYSQLI_ASSOC);
        }
    }

    public function execute()
    {
        $this->currentStatment->execute();
    }

    public function lastInsertId()
    {
        return $this->mysqli->insert_id;
    }
}
