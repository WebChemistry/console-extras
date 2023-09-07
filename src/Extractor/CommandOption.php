<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Extractor;

final class CommandOption
{

	/**
	 * @param string[] $types
	 */
	public function __construct(
		public readonly string $property,
		public readonly string $name,
		public readonly ?string $shortcut,
		public readonly bool $switch,
		public readonly bool $requireValue,
		public readonly bool $negatable,
		public readonly string $description,
		public readonly mixed $defaultValue,
		public readonly array $types,
		public readonly bool $nullable,
	)
	{
	}

}
