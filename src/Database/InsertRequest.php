<?php


namespace QueryBuilder;


class InsertRequest
{
    private static $valuesVerb = 'VALUES';
    /**
     * @var string
     */
    private string $table;
    private array $values = [];
    /**
     * @var array
     */
    private array $columns = [];

    /**
     * InsertRequest constructor.
     * @param string $table
     */
    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function values(...$values)
    {
        foreach ($values as $value) {
            $this->values[] = "'".$value."'";
        }
        return $this;
    }

    public function query()
    {
        return 'INSERT INTO ' .
            $this->table .
            ' ' .
            $this->stringifyColumns() .
            $this->stringifyValues() .
            '.';
    }

    private function stringifyValues()
    {

        return self::$valuesVerb .
            '(' .
            implode(', ', $this->values) .
            ')'
            ;
    }

    public function columns(...$columns)
    {
        foreach ($columns as $column) {
            $this->columns[] = $column;
        }
        return $this;
    }

    private function stringifyColumns()
    {
        $result = '';
        if (count($this->columns) > 0)
        {
            $result .= '(' .
                implode(', ', $this->columns) .
            ')' .
            ' ';

        }
        return $result;
    }
}