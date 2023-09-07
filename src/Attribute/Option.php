<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Option
{

	public function __construct(
		public readonly ?string $shortcut = null,
		public readonly bool $negatable = false,
	)
	{
	}

}
