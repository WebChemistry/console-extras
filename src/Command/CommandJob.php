<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Command;

final class CommandJob
{

	/**
	 * @param class-string $className
	 * @param mixed[] $arguments
	 */
	public function __construct(
		public readonly string $className,
		public readonly string $schedule,
		public readonly string $commandName,
		public readonly array $arguments = [],
		public readonly string $comment = '',
	)
	{
	}

}
