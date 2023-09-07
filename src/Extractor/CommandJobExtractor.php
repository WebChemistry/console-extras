<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Extractor;

use LogicException;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\Console\Command\Command;
use WebChemistry\ConsoleExtras\Attribute\Job;
use WebChemistry\ConsoleExtras\Command\CommandJob;

final class CommandJobExtractor
{

	/**
	 * @param Command[] $commands
	 * @return CommandJob[]
	 */
	public function extract(array $commands): array
	{
		$return = [];

		foreach ($commands as $command) {
			$reflection = new ReflectionClass($command);
			$attribute = $this->getAttribute($reflection, Job::class);

			if (!$attribute) {
				continue;
			}

			$commandName = $command->getName() ?? throw new LogicException(sprintf('Command %s has no name.', $command::class));

			$schedule = is_string($attribute->schedule) ? $attribute->schedule : $attribute->schedule->value;

			if (!is_string($schedule)) {
				throw new LogicException(sprintf('Schedule of command %s must be string.', $command::class));
			}

			$return[] = new CommandJob(
				$schedule,
				$attribute->name ?? $commandName,
				$commandName,
				$attribute->arguments,
				$attribute->options,
			);
		}

		return $return;
	}

	/**
	 * @template TAttribute of object
	 * @param ReflectionClass<object> $reflection
	 * @param class-string<TAttribute> $attributeName
	 * @return TAttribute|null
	 */
	private function getAttribute(ReflectionClass $reflection, string $attributeName): ?object
	{
		$attrs = $reflection->getAttributes($attributeName);

		return $attrs ? $attrs[0]->newInstance() : null;
	}

}
