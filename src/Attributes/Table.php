<?php

namespace Xudid\Entity\Attributes;

use Attribute;

#[Attribute]
class Table
{
	private string $name = '';

	public function __construct(string $name)
	{
		$this->name = $name;
	}

	public function getName(): string
	{
		return $this->name;
	}
}
