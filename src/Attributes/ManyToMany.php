<?php

namespace Xudid\Entity\Attributes;

use Attribute;

#[Attribute]
class ManyToMany
{
	private string $outClassname = '';
	public function __construct(string $outClassname)
	{
		$this->outClassname = $outClassname;
	}

	public function getToModel(): string
	{
		return $this->outClassname;
	}
}
