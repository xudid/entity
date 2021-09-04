<?php


namespace Entity\Database\QueryBuilder;


class DeleteRequest extends Request
{
	const TYPE = 'DELETE';

	private static string $requestVerb = 'DELETE';
	private static string $fromVerb = 'FROM';
	private static string $conditionVerb = 'WHERE';
	private static string $joinVerb = 'INNER JOIN %table2% ON %idTable1% = %idTable2%';

	private array $froms = [];
	private array $where = [];
	private array $joins = [];
	private array $binded = [];

	public function __construct(...$tables)
	{
		$this->from(...$tables);
	}

	private function stringyFroms()
	{
		return self::$fromVerb . ' ' . implode(',' , $this->froms);
	}

	private function stringifyWhere() : string
	{
		$return = '';
		if (count($this->where) > 0){
			$argument2 = str_replace('.', '_', $this->where[0]['argument1']);
			$return = self::$conditionVerb .
				' ' .
				$this->where[0]['argument1'] .
				' '.
				$this->where[0]['operator'] .
				' :'. $argument2 .
				' '
			;

			for ($i = 1; $i < count($this->where); $i ++) {
				$return .= $this->where[$i]['relation'] .
					' ' .
					$this->where[$i]['argument1'] .
					' '.
					$this->where[$i]['operator'] .
					' :'.$this->where[$i]['argument1'];
				$return .= ' ';
			}
		}
		return $return;
	}

	private function stringifyJoins()
	{
		$joinsString = '';
		foreach ($this->joins as $join) {
			$joinsString .= str_replace(
					['%table2%', '%idTable1%', '%idTable2%'],
					[$join['table2'], $join['idTable1'], $join['idTable2']],
					self::$joinVerb
				) . ' ';
		}
		return $joinsString;
	}

	public function query() : string
	{
		return self::$requestVerb .
			' ' .
			$this->stringyFroms() .
			' ' .
			$this->stringifyJoins() .
			$this->stringifyWhere() .
			' ' .
			';';
	}

	public function from(...$tables) : DeleteRequest
	{
		foreach ($tables as $table)
		{
			$this->froms[] = $table;
		}
		return $this;
	}

	public function where(string $argument1, $operator, $argument2, $relation = 'AND') : DeleteRequest
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
		$argument1 = str_replace('.', '_', $argument1);
		$this->binded[$argument1] = $argument2;
		return  $this;
	}

	public function join(string $table2, string $idTable1, string $idTable2) : DeleteRequest
	{
		$this->joins[] = ['table2' => $table2, 'idTable1' => $idTable1, 'idTable2' => $idTable2];
		return $this;
	}

	public function getBinded()
	{
		return $this->binded;
	}
}