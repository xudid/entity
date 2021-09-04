<?php

namespace Entity\Database\QueryBuilder;

use Entity\Database\Dao;
use Entity\Database\DataSource;
use Exception;
use Psr\Log\LoggerInterface;


/**
 * Class QueryBuilder
 * @package QueryBuilder
 */
class QueryBuilder implements QueryBuilderInterface
{
	private static ?QueryBuilderInterface $instance;
	/**
	 * @var Dao
	 */
	private Dao $dao;
	/**
	 * @var LoggerInterface
	 */
	private LoggerInterface $logger;

	public function __construct(DataSource $dataSource)
	{
		try {
			$this->dao = new Dao($dataSource);
		} catch (Exception $exception) {
			if ($this->logger) {
				$this->logger->error($exception->getMessage(),[__CLASS__ . ' ' . __METHOD__]);
			} else {
				// Todo
			}
		}
		self::$instance = $this;
	}

	public static function __callStatic($name, $arguments)
	{
		if (in_array($name, get_class_methods(__CLASS__))) {
			return self::$name($arguments);
		} else {
			throw  new Exception('Method : ' . $name . "doesn't exist in Query builder");
		}

	}

	public function select(...$fields): SelectRequest
	{
		$request = new SelectRequest(...$fields);
		$request->setDatabaseAbstractLayer(self::$instance->dao);
		return $request;
	}

	public function insert(string $table): InsertRequest
	{
		$request = new InsertRequest($table);
		$request->setDatabaseAbstractLayer(self::$instance->dao);
		return $request;
	}

	public function update(string $table) : UpdateRequest
	{
		$request = new UpdateRequest($table);
		$request->setDatabaseAbstractLayer(self::$instance->dao);
		return $request;
	}

	/**
	 * @param mixed ...$tables
	 * @return DeleteRequest
	 */
	public function delete(...$tables): DeleteRequest
	{
		$request = new DeleteRequest(...$tables);
		$request->setDatabaseAbstractLayer(self::$instance->dao);
		return $request;
	}

	public function enableDebug()
	{
		self::$instance->dao->enableDebug();
	}

	public function execute(Request $request)
	{
		return self::$instance->dao->execute($request);
	}

	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}
}
