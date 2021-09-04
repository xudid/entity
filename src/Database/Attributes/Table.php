<?php

namespace Entity\Database\Attributes;

use Attribute;

#[Attribute]
class Table
{
	private string $name = '';

	public function __construct(string $name)
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}
	
	
}