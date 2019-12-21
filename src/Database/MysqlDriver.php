<?php

namespace Entity\Database;
use PDO;
use PDOException;

class MysqlDriver extends PDO{
	private $dsn;
	private $server;
	private $port;
	private $database;
	private $user;
	private $password;
	public function __construct(array $config){
		$this->dsn = $config['mysql.dsn'];
		$this->server = $config['mysql.server'];
		$this->port = $config['mysql.port'];
		$this->database = $config['mysql.database'];
		$this->user = $config['mysql.user'];
		$this->password = $config['mysql.password'];
		$this->dsn = $this->dsn
			.':host='.$this->server
			.';port='.$this->port
			.';dbname='.$this->database;
		try {
			parent::__construct($this->dsn,$this->user,$this->password);
			$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		} catch (PDOException $e) {
        // todo : return an error message  and log the error
			echo $e->getMessage();
		}
	}
}
