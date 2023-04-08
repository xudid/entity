<?php

namespace Xudid\Entity\Attributes;

use Attribute;

#[Attribute]
class Column
{
	private string $type;
	public function __construct(string $type)
	{
		$this->type = $type;
	}

	public function getType(): string
	{
		return $this->type;
	}
}
