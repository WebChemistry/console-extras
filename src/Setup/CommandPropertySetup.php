<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Setup;

use Symfony\Component\Console\Input\InputInterface;
use WebChemistry\ConsoleExtras\Exception\InvalidCommandValueException;
use WebChemistry\ConsoleExtras\Extractor\CommandArgument;
use WebChemistry\ConsoleExtras\Extractor\CommandOption;

final class CommandPropertySetup
{

	/**
	 * @param CommandArgument[] $arguments
	 * @param CommandOption[] $options
	 * @return array<string, mixed>
	 */
	public function setup(array $arguments, array $options, InputInterface $input): array
	{
		$values = [];

		foreach ($arguments as $argument) {
			$values[$argument->property] = $this->convert($input->getArgument($argument->name), $argument);
		}

		foreach ($options as $option) {
			$values[$option->property] = $this->convert($input->getOption($option->name), $option);
		}

		return $values;
	}

	private function builtinConverter(mixed $value, CommandArgument|CommandOption $arg): mixed
	{
		$types = $arg->types;
		$isOption = $arg instanceof CommandOption;

		if ($isOption) {
			$return = $this->optionConverter($value, $arg);

			if ($return !== null) {
				return $return;
			}
		}

		if (in_array('bool', $types, true)) {
			if (is_bool($value)) {
				return $value;
			}

			if (is_string($value)) {
				$value = trim($value);

				if (strcasecmp($value, 'true') === 0 || $value === '1') {
					return true;
				}

				if (strcasecmp($value, 'false') === 0 || $value === '0') {
					return false;
				}
			}

			if (is_int($value)) {
				if ($value === 1) {
					return true;
				}

				if ($value === 0) {
					return false;
				}
			}
		}

		$int = in_array('int', $types, true);
		$float = in_array('float', $types, true);

		if ($int || $float) {
			if (is_string($value) && is_numeric($value)) {
				if (str_contains($value, '.')) {
					if (!$float) {
						return null;
					}

					return floatval($value);

				} else {
					if (!$int) {
						return floatval($value);
					}

					return intval($value);

				}
			}

			if (is_int($value)) {
				if (!$int) {
					return floatval($value);
				}

				return $value;
			}

			if (is_float($value)) {
				if (!$float) {
					return null;
				}

				return $value;
			}
		}

		if (in_array('string', $types, true)) {
			return $value;
		}

		return null;
	}

	private function convert(mixed $value, CommandArgument|CommandOption $arg): mixed
	{
		$return = $this->builtinConverter($value, $arg);

		if ($return === null && $value !== null) {
			throw new InvalidCommandValueException($arg->name, sprintf('Cannot convert value to %s.', implode('|', $arg->types)));
		}

		return $return;
	}

	private function optionConverter(mixed $value, CommandOption $option): mixed
	{
		if ($value === null && !$option->nullable && in_array('bool', $option->types, true)) {
			if ($option->negatable && is_bool($option->defaultValue)) {
				return $option->defaultValue;
			}
			
			return false;
		}

		return null;
	}

}
