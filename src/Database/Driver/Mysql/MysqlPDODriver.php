<?php

namespace Xudid\Entity\Database\Driver\Mysql;

use Exception;
use PDO;
use PDOStatement;
use Xudid\QueryBuilderContracts\Request\RequestInterface;
use Xudid\EntityContracts\Database\Driver\DriverInterface;
use Xudid\EntityContracts\Database\Driver\DriverException;

class MysqlPDODriver implements DriverInterface
{
    const FETCH_ASSOC = PDO::FETCH_ASSOC;
    const FETCH_CLASS = PDO::FETCH_CLASS;
    private PDO $pdo;
    private PDOStatement|false $currentStatment;
    private int $fetchMode = self::FETCH_ASSOC;
    private string $className = '';

    /**
     * @throws DriverException
     */
    public function __construct(
        private string $host,
        private string $database,
        private string $username,
        private string $port = '',
        private string $password = '',
    )
    {
        try {
            $dsn = 'mysql:host=%host%;port=%port%;dbname=%dbname%';
            $dsn = str_replace(
                ['%host%', '%port%', '%dbname%'],
                [$this->host, $this->port, $this->database],
                $dsn
            );

            $this->pdo = new PDO($dsn, $this->username, $this->password, [PDO::FETCH_ASSOC]);
        } catch (Exception $ex) {
            throw new DriverException();
        }
    }

    /**
     * @throws DriverException
     */
    public function query(string $request)
    {
        $fetchOptions = array_filter([
            $this->fetchMode,
            ($this->fetchMode == self::FETCH_CLASS && $this->className)
                ? $this->className
                : ''
        ]);

        $this->currentStatment = $this->pdo->query($request);
        if ($this->currentStatment === false) {
            throw new DriverException();
        }

        $result = $this->fetchAll($fetchOptions);
        if (empty($result)) {
            $this->currentStatment->rowCount();
        }

        return $result;
    }

    public function bind(RequestInterface $request)
    {
        $bindings = [
            'ok' => [],
            'error' => []
        ];

        $this->currentStatment = $this->pdo->prepare($request->toPreparedSql());
        foreach ($request->getBindings() as $param => $value) {
            $binded = $this->currentStatment->bindValue($param, $value);
            if (!$binded) {
                $bindings['error'][$param] = false;
            } else {
                $bindings['ok'][$param] = $value;
            }
        }

        return $bindings;
    }

    public function setFetchMode(int $fetchMode): static
    {
        $this->fetchMode = $fetchMode;
        return $this;
    }

    public function withClassName(string $className): static
    {
        $this->className = $className;
        return $this;
    }

    /**
     * @throws DriverException
     */
    public function fetch(): mixed
    {
        $fetchOptions = array_filter([
            $this->fetchMode,
            ($this->fetchMode == self::FETCH_CLASS && $this->className)
                ? $this->className
                : ''
        ]);
        $result = $this->currentStatment->fetch($fetchOptions);
        if ($result === false) {
            throw new DriverException();
        }
        return $result;
    }

    /**
     * @throws DriverException
     */
    public function fetchAll(): array
    {
        try {
            $result = $this->currentStatment->execute();

            if ($result === false) {
                throw new DriverException();
            }
            $fetchOptions = array_filter([
                $this->fetchMode,
                ($this->fetchMode == self::FETCH_CLASS && $this->className)
                ? $this->className
                : ''
            ]);
            return $this->currentStatment->fetchAll(...$fetchOptions);
        } catch (Exception $exception) {
            throw new DriverException();
        }
    }

    public function execute()
    {
        $this->currentStatment->execute();
    }

    public function lastInsertId(): mixed
    {
        return $this->pdo->lastInsertId();
    }
}
