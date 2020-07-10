<?php

namespace Entity\Migrations;

use Entity\Database\DaoInterface;
use Phinx\Config\Config;
use Phinx\Console\PhinxApplication;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class PhinxAdapter
{
    /**
     * @var array $config
     */
    private static array $config = [];

    /**
     * @var string $dbName
     */
    private string $dbName = '';

    /**
     * @var array $environments
     */
    private array $environments = [];

    /**
     * @var DaoInterface $dao
     */
    private DaoInterface $dao;

    /**
     * @var string $path
     */
    private string $path;

    /**
     * @var string $environment
     */
    private string $environment;

    /**
     * @var OutputInterface $outputBuffer
     */
    private OutputInterface $outputBuffer;

    /**
     * PhinxAdapter constructor.
     * @param DaoInterface $dao
     * @param string $path
     * @param string $environment
     */
    public function __construct(DaoInterface $dao, string $path, string $environment)
    {
        $this->dao = $dao;
        $this->path = $path;
        $this->environment = $environment;
        $this->outputBuffer = new NullOutput();
        $phinx = new PhinxApplication();
        $this->command = $phinx->find('migrate');
    }

    /**
     * @param string $dbName
     * @return PhinxAdapter
     */
    public function setDbName(string $dbName): PhinxAdapter
    {
        $this->dbName = $dbName;
        return $this;
    }

    /**
     * @param array $environments
     * @return PhinxAdapter
     */
    public function setEnvironments(array $environments): PhinxAdapter
    {
        $this->environments = $environments;
        return $this;
    }

    /**
     * @return $this
     */
    public function enableOutPut()
    {
        $this->outputBuffer = new BufferedOutput();
        return $this;
    }

    /**
     * @return bool|BufferedOutput|OutputInterface
     */
    public function getOutput()
    {
        if($this->outputBuffer instanceof BufferedOutput)
        {
            return $this->outputBuffer;
        }
        return false;
    }

    /**
     * @return int
     */
    public function run()
    {
        self::$config =  [
            'paths' => [
                'migrations' => $this->path . DIRECTORY_SEPARATOR . 'migrations',
                'seeds' => $this->path . DIRECTORY_SEPARATOR . 'seeds'
            ],

            'environments' => [
                'default_environment' => 'development',
                'development' => [
                    'name' => $this->dbName,
                    'connection' => $this->dao->getDriver()
                ],
                'production' => [
                    'name' => $this->dbName,
                    'connection' => $this->dao->getDriver()
                ],
                'testing' => [
                    'name' => $this->dbName,
                    'connection' => $this->dao->getDriver()
                ]
            ],
        ];
        $this->command->setConfig(new Config(self::$config));
        try {
            return $this->command->run(new ArrayInput([]), $this->outputBuffer);
        } catch (ExceptionInterface $e) {
            dump($e,$this->outputBuffer);
        } catch (\Exception $e) {
            dump($e);
        }
    }
}
