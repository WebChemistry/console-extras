<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Command;

final class CommandJob
{

	/**
	 * @param mixed[] $arguments
	 * @param mixed[] $options
	 */
	public function __construct(
		public readonly string $className,
		public readonly string $schedule,
		public readonly string $name,
		public readonly string $commandName,
		public readonly array $arguments = [],
		public readonly array $options = [],
		public readonly string $comment = '',
	)
	{
	}

}
