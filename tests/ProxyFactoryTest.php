<?php

use PHPUnit\Framework\TestCase;
use Xudid\Entity\Model\Proxy\ProxyFactory;

class ClassOfTest
{

}
class ProxyFactoryTest extends TestCase
{
    public function testCreateDynamicProxyReturnInstanceOfHeritedClass()
    {
        $proxyFactory = new ProxyFactory();
        $class = $proxyFactory->create(ClassOfTest::class);
        $this->assertInstanceOf(ClassOfTest::class, $class);
    }
}
