<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Argument
{

	public function __construct()
	{
	}

}
