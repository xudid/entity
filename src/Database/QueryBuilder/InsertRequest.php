<?php


namespace Entity\Database\QueryBuilder;


class InsertRequest extends Request
{
    const TYPE = 'INSERT';
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
     * @var array
     */
    private array $binded = [];

    /**
     * InsertRequest constructor.
     * @param string $table
     */
    public function __construct(string $table)
    {
        $this->table = $table;
        $this->TYPE = InsertRequest::TYPE;
    }

    public function values(array $values)
    {
            foreach ($values as $key =>$value) {
                $this->binded[$key] = $value;
            }
        return $this;
    }

    public function query()
    {
        return 'INSERT INTO ' .
            $this->table .
            ' ' .
            $this->stringifyColumns() .
            $this->stringifyValues() ;
    }

    private function stringifyValues()
    {
        return self::$valuesVerb .
            '(' . implode(',', $this->values) . ')'
            ;
    }

    public function columns(...$columns)
    {
        if (is_array($columns)) {
            $this->columns = $columns;
            foreach ($columns as $column) {
                $this->values[] = ":" . $column ;
            }
        } else {
            foreach ($columns as $column) {
                $this->columns[] = $column;
            }
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getBinded(): array
    {
        return $this->binded;
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