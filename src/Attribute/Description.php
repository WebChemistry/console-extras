<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_CLASS)]
final class Description
{

	public function __construct(
		public readonly string $description,
	)
	{
	}

}
