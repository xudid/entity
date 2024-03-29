<?php

namespace Database;

use PHPUnit\Framework\TestCase;
use Xudid\Entity\Model\LazyLoader;

class LazyLoaderTest extends TestCase
{
    public function testConstruct()
    {
        $object = new class {
            public function hello($name)
            {
                return 'Hello ' . $name;
            }
        } ;
        $loader = new LazyLoader($object,'hello', ['didux']);
        $this->assertInstanceOf(LazyLoader::class, $loader);
    }

    public function testInvoke()
    {
        $object = new class {
            public function hello($name)
            {
                return 'Hello ' . $name;
            }
        } ;
        $loader = new LazyLoader($object,'hello', ['didux']);
        $result = $loader();
        $this->assertStringContainsString('Hello didux', $result);
    }
}
