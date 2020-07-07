<?php

namespace Entity\Database\QueryBuilder;

use Entity\Database\Dao;
use Entity\Database\DataSource;
use Exception;


/**
 * Class QueryBuilder
 * @package QueryBuilder
 */
class QueryBuilder implements QueryBuilderInterface
{
    private static ?QueryBuilderInterface $instance;
    /**
     * @var Dao
     */
    private Dao $dao;

    public function __construct(DataSource $dataSource)
    {
        try {
            $this->dao = new Dao($dataSource, '');
        } catch (Exception $e) {
            dump($e);
        }
        self::$instance = $this;
    }

    public static function __callStatic($name, $arguments)
    {
        if (in_array($name, get_class_methods(__CLASS__))) {
            return self::$name($arguments);
        } else {
            throw  new Exception('Method : ' . $name . "doesn't exist in Query builder");
        }

    }

    public function select(...$fields): SelectRequest
    {
        return new SelectRequest(...$fields);
    }

    public function insert(string $table): InsertRequest
    {
        return new InsertRequest($table);
    }

    public function update(string $table) : UpdateRequest
    {
        return new UpdateRequest($table);
    }

    /**
     * @param mixed ...$tables
     * @return DeleteRequest
     */
    public function delete(...$tables): DeleteRequest
    {
        return new DeleteRequest(...$tables);
    }

    public function enableDebug()
    {
        self::$instance->dao->enableDebug();
    }

    public function execute(Request $request)
    {
        return self::$instance->dao->execute($request);
    }
}
