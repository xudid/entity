<?php
namespace Entity\Database;

use Entity\Database\QueryBuilder\Request;

/**
 * Interface DaoInterface
 * @package Entity\Database
 */
interface DaoInterface
{
    /**
     * @param Request $request
     * @param string $className
     * @return mixed
     */
    public function execute(Request $request, string $className );

    public function enableDebug();

    public function getDriver();

}

