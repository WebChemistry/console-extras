<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Extractor;

use LogicException;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use WebChemistry\ConsoleExtras\Attribute\Job;
use WebChemistry\ConsoleExtras\Command\CommandJob;
use WebChemistry\ConsoleExtras\Command\CommandJobGroup;
use WebChemistry\ConsoleExtras\Group\GroupedSchedule;

final class CommandJobExtractor
{

	/**
	 * @param Command[] $commands
	 * @return CommandJobGroup[]
	 */
	public function extract(array $commands): array
	{
		$groups = [];

		foreach ($commands as $command) {
			$reflection = new ReflectionClass($command);
			$attribute = $this->getAttribute($reflection, Job::class);

			if (!$attribute) {
				continue;
			}

			$commandName = $command->getName() ?? throw new LogicException(sprintf('Command %s has no name.', $command::class));

			if ($attribute->schedule instanceof GroupedSchedule) {
				$group = $attribute->schedule->getGroupName();
				$schedule = $attribute->schedule->getSchedule();
				$options = $attribute->schedule->getOptions();
			} else {
				$schedule = is_string($attribute->schedule) ? $attribute->schedule : $attribute->schedule->value;
				$group = '';
				$options = $attribute->options;
			}

			if (!is_string($schedule)) {
				throw new LogicException(sprintf('Schedule of command %s must be string.', $command::class));
			}

			if (!isset($groups[$group])) {
				$groupClass = new CommandJobGroup($schedule, $options, $group);

				if ($group) {
					$groups[$group] = $groupClass;
				} else {
					$groups[] = $groupClass;
				}
			} else {
				$groupClass = $groups[$group];
			}

			$groupClass->addJob(new CommandJob(
				$command::class,
				$schedule,
				$commandName,
				$attribute->arguments,
				$command->getDescription(),
			), $command, $command->getDescription());
		}

		return $groups;
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
