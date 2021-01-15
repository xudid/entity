<?php


namespace Entity\Metadata\Holder;


use Exception;
use ReflectionObject;
use ReflectionClass;

class ProxyFactory
{
	private string $fileCachePath;

	/**
	 * @param $object
	 * @return object
	 */
	public function create($object)
	{
		$objectReflection = new ReflectionObject($object);
		$namespace = $objectReflection->getNamespaceName();
		$objectShortClassName = $objectReflection->getShortName();
		$shortClassName = $objectShortClassName . 'Proxy';
		$className = $objectReflection->getName() . 'Proxy';
		$fileClassName = str_replace('\\', DIRECTORY_SEPARATOR, $className);
		$fileName = $this->fileCachePath . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $fileClassName . '.php';
		if (!file_exists($fileName)) {
			$code = $this->generateCode($namespace,$objectShortClassName, $shortClassName);
			$this->generateClassFile($code, $fileName);
		}
		require_once $fileName;
		try {
			$proxyRelfection = new ReflectionClass($className);
			return $proxyRelfection->newInstance($object);
		} catch (Exception $exception)
		{
			echo '<pre>' . var_dump($exception);
		}
	}

	/**
	 * @param string $path
	 */
	public function setCachePath(string $path): ProxyFactory
	{
		$this->fileCachePath = $path;
		return $this;
	}

	/**
	 * @param string $namespace
	 * @param string $objectShortClassName
	 * @param string $shortClassName
	 * @return string
	 */
	private function generateCode(string $namespace, string $objectShortClassName, string $shortClassName) : string
	{
		$code = "<?php
                namespace $namespace;
                
                 use Entity\Database\LazyLoader;
                 use Serializable;
                
                class $shortClassName extends $objectShortClassName implements Serializable
                {" .'
                 
                    private  $wrapped;
                    private $propertyLoaders = [];
                    private $propertyLoaded = [];
                    public function __construct($object = null)
                    {
                        if ($object) {
                            $this->wrapped = $object;
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
                        if ($value instanceof LazyLoader) {
                            $this->propertyLoaders[$name] = $value;
                            return $this;
                        } else {
                            $setter = "set" . ucfirst($name);
                            $this->wrapped->$setter($value);
                            return $this;
                        }
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
		//echo '<pre>' . var_dump($code);
		return $code;
	}

	/**
	 * @param $code
	 * @param $filename
	 */
	private function generateClassFile($code, $filename)
	{
		$this->file_force_contents($filename ,$code);
	}

	/**
	 * @param $fileFullPath
	 * @param $contents
	 */
	private function file_force_contents($fileFullPath, $contents){
		if (!is_dir(dirname($fileFullPath))) {
			mkdir(dirname($fileFullPath), 0777,true);
		}
		file_put_contents($fileFullPath, $contents);
	}
}
