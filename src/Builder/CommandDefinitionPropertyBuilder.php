<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Builder;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use WebChemistry\ConsoleExtras\Extractor\CommandArgument;
use WebChemistry\ConsoleExtras\Extractor\CommandOption;

final class CommandDefinitionPropertyBuilder
{

	/**
	 * @param CommandArgument[] $arguments
	 * @param CommandOption[] $options
	 */
	public function build(array $arguments, array $options, InputDefinition $definition): void
	{
		foreach ($arguments as $argument) {
			$mode = $argument->required ? InputArgument::REQUIRED : InputArgument::OPTIONAL;

			$definition->addArgument(new InputArgument(
				$argument->name,
				$mode,
				$argument->description,
				$argument->defaultValue, // @phpstan-ignore-line
			));
		}

		foreach ($options as $option) {
			if ($option->switch) {
				$mode = InputOption::VALUE_NONE;

				if ($option->negatable) {
					$mode |= InputOption::VALUE_NEGATABLE;
				}

			} else {
				$mode = $option->requireValue ? InputOption::VALUE_REQUIRED : InputOption::VALUE_OPTIONAL;

			}

			$definition->addOption(new InputOption(
				$option->name,
				$option->shortcut,
				$mode,
				$option->description,
				$option->switch ? null : $option->defaultValue, // @phpstan-ignore-line
			));
		}
	}

}
