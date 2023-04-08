<?php

namespace Xudid\Entity\Model;

class LazyLoader
{
    private $loader;
    private $function;
    private array $args;

    /**
     * LazyLoader constructor.
     * @param $loader
     * @param $function
     */
    public function __construct($loader, $function, array $args)
    {
        $this->loader = $loader;
        $this->function = $function;
        $this->args = $args;
    }

    public function __invoke()
    {
        $function  = $this->function;
        return call_user_func_array([$this->loader, $function],$this->args);
    }
}
