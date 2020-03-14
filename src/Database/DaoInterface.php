<?php
namespace Entity\Database;

use Entity\Database\QueryBuilder\Request;

/**
 *
 */
interface DaoInterface
{

    /**
     * @return string
     */
    public function getClassNamespace(): string;


    /**
     * @param Request $request
     * @return mixed
     */
    public function execute(Request $request);

}

