<?php


namespace Entity\Model;


use Entity\Database\Dao;
use Entity\Database\DaoInterface;
use Entity\Database\DataSourceInterface;

class ManagerFactory
{
    /**
     * @var DataSourceInterface
     */
    private DataSourceInterface $dataSource;

    /**
     * ManagerFactory constructor.
     * @param DataSourceInterface $dataSource
     */
    public function __construct(DataSourceInterface $dataSource)
    {
        $this->dataSource = $dataSource;
    }

    /**
     * @param string $className
     * @return ModelManager
     * @throws \Exception
     */
    public function getManager(string $className)
    {
        try {
            $dao = new Dao($this->dataSource, $className);
        } catch (\Exception $e) {
            dump($e);
        }
        return new ModelManager($dao);
    }
}