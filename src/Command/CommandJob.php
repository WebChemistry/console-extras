<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Command;

final class CommandJob
{

	/**
	 * @param class-string $className
	 * @param mixed[] $arguments
	 * @param mixed[] $options
	 */
	public function __construct(
		public readonly string $className,
		public readonly string $schedule,
		public readonly string $commandName,
		public readonly array $arguments,
		public readonly string $comment,
		public readonly ?string $groupName,
		public readonly string $slug,
		public readonly array $options,
	)
	{
	}

}
