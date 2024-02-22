<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Extractor\Serializer;

final class UnserializedJob
{

	/**
	 * @param mixed[] $arguments
	 */
	public function __construct(
		public readonly string $className,
		public readonly ?string $commandName, // make non-nullable
		public readonly array $arguments,
	)
	{
	}

}
