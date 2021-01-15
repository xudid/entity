<?php

namespace Entity\Database;

use Doctrine\Common\Inflector\Inflector;
use Entity\Database\QueryBuilder\Request;
use Exception;
use \PDOStatement;

/**
 * Class Executer
 * @package Entity\Database
 */
class Executer implements ExecuterInterface {
	/**
	 * @var DriverInterface $driver
	 */
	protected DriverInterface $driver;

	/**
	 * @var string $className
	 */
	protected string $className;

	/**
	 * @var PDOStatement $statment
	 */
	protected PDOStatement $statment;

	/**
	 * @var Request $request
	 */
	protected Request $request;

	/**
	 * @var bool $debug
	 */
	protected bool $debug = false;
	/**
	 * @var bool $statmentResult
	 */
	protected $statmentResult = false;


	protected $connexion;
	/**
	 * InsertExecuter constructor.
	 * @param DriverInterface $driver
	 */
	public function __construct(DriverInterface $driver)
	{
		$this->driver = $driver;
	}

	/**
	 * @param string $className
	 * @return Executer
	 */
	public function className(string $className) : Executer
	{
		$this->className = $className;
		return $this;
	}

	/**
	 * @param Request $request
	 * @return Executer
	 */
	public function request(Request $request) : Executer
	{
		$this->request = $request;
		return $this;
	}

	/**
	 * @return Executer
	 */
	public function enableDebug()  : Executer
	{
		$this->debug = true;
		return $this;
	}

	public function execute()
	{
		$this->connexion = $this->driver->getConnexion();
		$this->statment = $this->connexion->prepare($this->request->query());

		$bindings=['ok' => [], 'error' => []];
		foreach ($this->request->getBinded() as $field => $value) {
			$binded = $this->statment->bindValue(Inflector::tableize($field), $value);
			if ($this->debug && !$binded) {
				$bindings['error'][$field] = false;
			} else {
				$bindings['ok'][$field] = $value;
			}
		}

		if ($this->debug) {
			echo '<pre>';

			$this->statment->debugDumpParams();
		}
		try {
			$this->statmentResult = $this->statment->execute();
			if ($this->debug) {
				echo '<pre>';
				$this->statment->debugDumpParams();
				var_dump('result : '. $this->statmentResult);
				var_dump('row count : ' . $this->statment->rowCount ());
				var_dump('error code : ' . $this->statment->errorCode());
				var_dump('error info 1 : ' . $this->statment->errorInfo()[0]);
				var_dump('error info 2 : ' . $this->statment->errorInfo()[1]);
				var_dump( 'bindings', $bindings);
				var_dump( 'uniq_id', $this->id);
			}
		} catch (\PDOException $ex) {
			var_dump($ex->getMessage()   . __FILE__);
		}
	}
}
