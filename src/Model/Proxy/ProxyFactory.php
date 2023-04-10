<?php


namespace Xudid\Entity\Model\Proxy;

use Xudid\Entity\Model\LazyLoader;
use Xudid\Entity\Model\Model;
use Xudid\EntityContracts\Metadata\AssociationInterface;
use Exception;
use Psr\Log\LoggerInterface;
use ReflectionObject;
use ReflectionClass;

class ProxyFactory
{
	private string $fileCachePath;
	private Model $wrapped;
	private array $loaders = [];
	private LoggerInterface $logger;

	public function create(): object
	{
        try {
            $wrappedReflection = new ReflectionObject($this->wrapped);
            $namespace = $wrappedReflection->getNamespaceName();
            $wrappedShortClassName = $wrappedReflection->getShortName();
            $shortClassName = $wrappedShortClassName . 'Proxy';
            $className = $wrappedReflection->getName() . 'Proxy';
            $fileClassName = str_replace('\\', DIRECTORY_SEPARATOR, $className);
            $fileName = $this->fileCachePath . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $fileClassName . '.php';
            if (!file_exists($fileName)) {
                $code = $this->generateCode($namespace,$wrappedShortClassName, $shortClassName);
                $this->generateClassFile($code, $fileName);
            }
		    require_once $fileName;

			$proxyRelfection = new ReflectionClass($className);

			return $proxyRelfection->newInstance($this->wrapped, $this->loaders);
		} catch (Exception $exception)
		{
			if ($this->logger) {
				$this->logger->debug($exception->getMessage());
			}
		}
        throw new Exception('Failed to generate proxy class for : ' . $className);
	}

	public function setCachePath(string $path): ProxyFactory
	{
		$this->fileCachePath = $path;
		return $this;
	}

	public function setWrapped(Model $model)
	{
		$this->wrapped = $model;
	}

	public function addLoader(AssociationInterface $association, LazyLoader $loader)
	{
        $propertyName = $association->getName();
		$this->loaders[$propertyName] = $loader;
	}

	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
        return $this;
	}

	private function generateCode(string $namespace, string $wrappedShortClassName, string $shortClassName) : string
	{
		$code = "<?php
                namespace $namespace;
                
                 use Entity\Database\LazyLoader;
                 use Serializable;
                
                class $shortClassName extends $wrappedShortClassName implements Serializable
                {" .'
                 
                    private  $wrapped;
                    private $propertyLoaders = [];
                    private $propertyLoaded = [];
                    public function __construct($wrapped = null, array $loaders = [])
                    {
                        if ($wrapped) {
                            $this->wrapped = $wrapped;
                            $this->propertyLoaders = $loaders;
                            $this->cleanProperties();
                        }
                    }';
		$code .= 'public function __get($name)
                 {
                     if (!array_key_exists($name, $this->propertyLoaded)
                         && array_key_exists($name, $this->propertyLoaders)) {
                         $loader = $this->propertyLoaders[$name];
                         if ($loader) {
                             $value = $loader();
                             $setter = "set" . ucfirst($name);
                             $this->wrapped->$setter($value);
                             $this->propertyLoaded[$name] = true;
                             $method = "get".ucfirst($name);
                             return $this->wrapped->$method();
                         }
                     }
                     $method = "get".ucfirst($name);
                     return $this->wrapped->$method();
                 }';

		$code .= 'public function __set($name, $value)
                  {
                      $setter = "set" . ucfirst($name);
                      $this->wrapped->$setter($value);
                      return $this;
                        
                  }';
		$code .= 'public function serialize()
                 {
                     foreach ($this->propertyLoaders as $propertyName => $propertyLoader) {
                         $value = $propertyLoader();
                         $setter = "set" . ucfirst($propertyName);
                         $this->wrapped->$setter($value);
                         $this->propertyLoaded[$propertyName] = true;
                     }
                     return serialize([
                          "wrapped" => $this->wrapped,
                          "propertyLoaded" => $this->propertyLoaded,
                     ]);
                 }';
		$code .= 'public function unserialize($serialized)
                 {
                     $data = unserialize($serialized);
                     $this->cleanProperties();
                     $this->wrapped = $data["wrapped"];
                     $this->propertyLoaded = $data["propertyLoaded"];
                 }';
		$code .= 'protected function cleanProperties()
                 {
                     $selfReflection = new \ReflectionClass(parent::getClass());
                     $selfProperties = $selfReflection->getProperties();
                     foreach ($selfProperties as $selfProperty) {
                         $propertyName = $selfProperty->getName();
                 	     if ($selfProperty->isPrivate()) {
                             throw new \Exception("Proxied object must have protected properties because proxy inherits of his class");
                         }
                         if ($propertyName != "wrapped") {
                             unset($this->$propertyName);
                         }
                     }
                 }';
		$code .= '
             }';
		return $code;
	}

	private function generateClassFile(string $code, string $filename)
	{
		$this->file_force_contents($filename ,$code);
	}

	private function file_force_contents(string $fileFullPath, string $contents){
		if (!is_dir(dirname($fileFullPath))) {
			mkdir(dirname($fileFullPath), 0777,true);
		}
		file_put_contents($fileFullPath, $contents);
	}
}
