<?php


namespace Xudid\Entity\Database\Driver\Mongo;

use MongoDB\Driver\Manager;
use Xudid\EntityContracts\Database\Driver\DataSourceInterface;
use Xudid\EntityContracts\Database\Driver\DriverInterface;

class MongoDBDriver  implements DriverInterface
{
    //mongodb://myDBReader:D1fficultP%40ssw0rd@mongodb0.example.com:27017/admin
    const URL_PREFIX = 'mongodb://';
    private string $scheme = self::URL_PREFIX .'#USER#:#PASSWORD#@#HOST#;port=#PORT#';
    private string $server;
    private string $port;
    /**
     * @var string
     */
    private string $database;
    /**
     * @var string|string[]
     */
    private $dsn;
    private $user;
    private $password;

    public function __construct(DataSourceInterface $dataSource)
    {
        $config = $dataSource->getConfig();
        $this->database = $config['mongo.database'];
        $this->server = $config['mongo.server'];
        $this->port = $config['mongo.port'] ? $config['mongo.port'] :27017;
        $this->user = $config['mongo.user'];
        $this->password = $config['mongo.password'];
        $this->generateUrl();
        $this->manager = new Manager("mongodb://localhost:27017");
    }

    public function getConnectionUrl(): string
    {
        return $this->dsn;
    }

    public function getConnexion()
    {
        // TODO: Implement getConnection() method.
    }

    private function generateUrl()
    {
        $url = self::URL_PREFIX;
        if ($this->user && $this->password) {
            $url .= $this->user . ':' . $this->password . '@';
        }
        $url .= $this->server . ':' . $this->port;

        if ($this->database) {
            $url .= '/' . $this->port;
        }
        $this->dsn = $url;
    }
}