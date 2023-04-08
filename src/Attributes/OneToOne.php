<?php


namespace Xudid\Entity\Attributes;

use Attribute;

#[Attribute]
class OneToOne
{
	private string $outClassname = '';
	public function __construct(string $outClassname)
	{
		$this->outClassname = $outClassname;
	}

	public function getOutClassname(): string
	{
		return $this->outClassname;
	}
}
