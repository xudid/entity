<?php

namespace Entity\Database\Attributes;

use Attribute;

#[Attribute]
class Column
{
	private string $type;
	public function __construct(string $type)
	{
		$this->type = $type;
	}

	/**
	 * @return string
	 */
	public function getType(): string
	{
		return $this->type;
	}
	
	
	
	
}