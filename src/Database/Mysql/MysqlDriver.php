<?php

namespace Entity\Database\Mysql;

use Entity\Database\DataSourceInterface;
use Entity\Database\DriverInterface;
use PDO;
use PDOException;

class MysqlDriver extends PDO implements DriverInterface {
	private string $dsn ;
	private string $server;
	private string $port;
	private string $database;
	private string $user;
	private string $password;
	private string $scheme = 'mysql:host=#HOST#;port=#PORT#;dbname=#DB#';
    private $attributes = [];

    public function __construct(DataSourceInterface $dataSource){
	    $config = $dataSource->getConfig();
		$this->server = $config['mysql.server'];
		$this->port = $config['mysql.port'];
		$this->database = $config['mysql.database'];
		$this->user = $config['mysql.user'];
		$this->password = $config['mysql.password'];
		$this->attributes = $config['mysql.attributes'];
		$this->dsn = str_replace(
		    ['#HOST#', '#PORT#', '#DB#'],
            [$this->server, $this->port ? $this->port : 3306, $this->database],
            $this->scheme
        );
		try {
			parent::__construct($this->dsn,$this->user,$this->password);
			foreach ($this->attributes as $name => $value) {
				$this->setAttribute($name, $$value);
			}

		} catch (PDOException $e) {
        // todo : return an error message  and log the error
            echo $e->getMessage();
		}
	}

	public function getConnectionUrl() : string
    {
        $url = str_replace(
            ['#USER#', '#PASSWORD#', '#HOST#', '#DB#'],
            [$this->user, $this->password, $this->server, $this->database],
            'mysql://#USER#:#PASSWORD#@#HOST#/#DB#');
        return $url ;
    }

    public function getConnection()
    {
        return $this;
    }
}
