<?php

namespace Xudid\Entity\Attributes;

use Attribute;

#[Attribute]
class Id
{
	private bool $autoIncrement;
	
	public function __construct(bool $autoIncrement = true)
	{
		$this->autoIncrement = $autoIncrement;
	}

    public function isAutoIncrement()
    {
        return $this->autoIncrement;
    }
}
