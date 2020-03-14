<?php

namespace Entity\Database\QueryBuilder;

/**
 * Class QueryBuilder
 * @package QueryBuilder
 */
class QueryBuilder
{
  public function select(...$fields) :SelectRequest
  {
    return new SelectRequest($fields);
  }

  public  function insert(string $table) : InsertRequest
  {
    return new InsertRequest($table);
  }
}

