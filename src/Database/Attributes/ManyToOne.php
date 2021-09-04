<?php

namespace Entity\Database\Attributes;

use Attribute;

#[Attribute]
class ManyToOne
{
	private string $outClassname = '';
	public function __construct(string $outClassname)
	{
		$this->outClassname = $outClassname;
	}

	/**
	 * @return string
	 */
	public function getOutClassname(): string
	{
		return $this->outClassname;
	}
}
