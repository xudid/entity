<?php


namespace QueryBuilder;


use Entity\Database\QueryBuilder\Request;

class UpdateRequest extends Request
{
    private static string $updatePattern = 'UPDATE %table% SET';
    /**
     * @var string
     */
    private string $table;
    private array $sets = [];

    /**
     * UpdateRequest constructor.
     * @param string $table
     */
    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function query()
    {
        return str_replace('%table%', $this->table, self::$updatePattern);
    }

    public function set(string $column, $value) : UpdateRequest
    {
        $this->sets[$column] =  $value;
        return $this;
    }
}