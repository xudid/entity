<?php


use Entity\Metadata\Holder\ProxyFactory;
use PHPUnit\Framework\TestCase;


class ClassOfTest
{

}
class ProxyFactoryTest extends TestCase
{

    public function testCreateDynamicProxyReturnInstanceOfHeritedClass()
    {
        $proxyFactory = new ProxyFactory();
        $class = $proxyFactory->createProxy(ClassOfTest::class);
        $this->assertInstanceOf(ClassOfTest::class, $class);
    }
}
