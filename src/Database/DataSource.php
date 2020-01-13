<?php


namespace Entity\Database;



abstract class DataSource implements DataSourceInterface
{
    private  string $name = "";
    private array $config = [];

    /**
     * DataSource constructor.
     * @param string $name
     * @param array $config
     */
    public function __construct(string $name, array $config)
    {
        $this->name = $name;
        $this->config = $config;
    }


    public function getName(): string
    {
        return $this->name;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @return mixed
     */
    public abstract function getDriver() : DriverInterface;
}
