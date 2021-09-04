<?php


namespace Entity\Database\Attributes;

use Attribute;

#[Attribute]
class Id
{
	private bool $autoIncrement;
	
	public function __construct(bool $autoIncrement = true)
	{
		$this->autoIncrement = $autoIncrement;
	}
}