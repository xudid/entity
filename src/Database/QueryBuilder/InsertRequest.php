<?php

namespace Entity\Database\QueryBuilder;

/**
 * Class InsertRequest
 * @package Entity\Database\QueryBuilder
 */
class InsertRequest extends Request
{
    const TYPE = 'INSERT';
    /**
     * @var string $valuesVerb
     */
    private static string $valuesVerb = 'VALUES';

    /**
     * @var string $table
     */
    private string $table;

    /**
     * @var array $values
     */
    private array $values = [];

    /**
     * @var array $columns
     */
    private array $columns = [];

    /**
     * @var array $binded
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

    /**
     * @param array $values
     * @return $this
     */
    public function values(array $values)
    {
            foreach ($values as $key =>$value) {
                $this->binded[$key] = $value;
            }
        return $this;
    }

    /**
     * @return string
     */
    public function query()
    {
        return 'INSERT INTO ' .
            $this->table .
            ' ' .
            $this->stringifyColumns() .
            $this->stringifyValues() ;
    }

    /**
     * @return string
     */
    private function stringifyValues()
    {
        return self::$valuesVerb .
            '(' . implode(',', $this->values) . ')'
            ;
    }

    /**
     * @param mixed ...$columns
     * @return $this
     */
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

    /**
     * @return string
     */
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
