<?php


namespace Entity\Database\QueryBuilder;


class UpdateRequest extends Request
{
    const TYPE = 'UPDATE';
    private static string $updatePattern = 'UPDATE %table% SET';
    private static string $conditionVerb = 'WHERE';
    /**
     * @var string
     */
    private string $table;
    private array $sets = [];
    /**
     * @var array
     */
    private $where = [];
    private $setBinded = [];

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
        $query = str_replace('%table%', $this->table, self::$updatePattern);
        $query .= $this->stringifySets();
        $query .= $this->stringifyWhere();
        return $query;
    }

    public function set(string $column, $value) : UpdateRequest
    {
        $this->sets[$column] =  $value;
        return $this;
    }

    public function where(string $argument1, $operator, $argument2, $relation = 'AND') : UpdateRequest
    {
        if (count($this->where) == 0) {
            $this->where[] = [
                'argument1' => $argument1,
                'operator' => $operator,
            ];
        } else {
            $this->where[] = [
                'argument1' => $argument1,
                'operator' => $operator,
                'relation' => $relation
            ];
        }
        $this->binded[$argument1] = $argument2;
        return  $this;
    }

    /**
     * @return array
     */
    public function getBinded(): array
    {
        return $this->binded;
    }

    private function stringifySets() : string
    {
        $sets = ' ';
        foreach ($this->sets as $column => $value) {
            $sets .= $column . ' = ' . "'". $value . "'" ;
            if (array_key_last($this->sets) != $column) {
                $sets .= ' , ';
            }

        }
        return $sets;
    }


    private function stringifyWhere() : string
    {
        $return = '';
        if (count($this->where) > 0){
            $return = self::$conditionVerb .
                ' ' .
                $this->where[0]['argument1'] .
                ' '.
                $this->where[0]['operator'] .
                ' :'.$this->where[0]['argument1'] .
                ' '
            ;

            for ($i = 1; $i < count($this->where); $i ++) {
                $return .= $this->where[$i]['relation'] .
                    ' ' .
                    $this->where[$i]['argument1'] .
                    ' '.
                    $this->where[$i]['operator'] .
                    ' :'.$this->where[$i]['argument1'];
                if ($i < (count($this->where)-3)) {
                    $return .= ' ';
                }
            }
        }
        return $return;
    }


}