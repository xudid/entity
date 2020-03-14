<?php


namespace Entity\Metadata\Holder;


use ReflectionClass;
use ReflectionObject;

class ProxyFactory
{
    public function createProxy($object)
    {
        $r = new ReflectionObject($object);
        $namespace = $r->getNamespaceName();
        $objectShortClassName = $r->getShortName();
        $shortClassName = $objectShortClassName . 'Proxy';
        $className = $r->getName() . 'Proxy';
         $this->root = dirname($_SERVER['DOCUMENT_ROOT']);
        $fileName = $this->root . DIRECTORY_SEPARATOR .  'classes' . DIRECTORY_SEPARATOR . $className . '.php';

        if (!file_exists($fileName)) {
           $this->generateProxyCLassFile($namespace,$objectShortClassName, $shortClassName, $fileName);
        }
        require_once $fileName;
        try {
            $r = new ReflectionClass($className);
            $proxy =  $r->newInstance($object);
            /*$columns = array_keys($object::getColumns());
            foreach ($columns as $column) {
                $setter = 'set' . ucfirst($column);
                $getter = 'get' . ucfirst($column);
                $proxy->$setter($object->$getter());
            }*/
            return $proxy;
        } catch (\Exception $exception)
        {
            dump($exception);
        }
    }

    private function generateProxyCLassFile($namespace, $objectShortClassName, $shortClassName, $filename)
    {
        $code = "<?php
                namespace $namespace;
                class $shortClassName extends $objectShortClassName{" .'
                 
                    private  $wrapped;
                    public function __construct(...$object)
                    {
                        $this->wrapped = $object;
                    }
                    
                   
                    public function __call($name, $args) 
                    {
                        $args=implode(",",$args);
                        return $this->wrapped->$name($args);
                    }
                    
                    public static function __callStatic($name, $args)
                    {
                        return $this->wrapped::$name($args);
                    }
                    
                    public function __get($name)
                    {
                        $method = "get".ucfirst($name);
                        return $this->wrapped->$method();
                    }
                    
                }';
        $this->file_force_contents($filename ,$code);
    }

    private function file_force_contents($dir, $contents){
        $parts = explode('\\', $dir);
        $file = array_pop($parts);
        $dir = '';
        $start = 0;
        if (PHP_OS_FAMILY == 'Windows') {
            $dir = $parts[$start];
            $start = 1;
        }
        for($i = $start;$i < count($parts) ;$i++) {
            $part = $parts[$i];
            if(!is_dir($dir .= DIRECTORY_SEPARATOR ."$part")) {
                mkdir($dir);
            }
        }
        file_put_contents($dir . DIRECTORY_SEPARATOR . $file, $contents);
    }
}