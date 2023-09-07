<?php declare(strict_types = 1);

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tester\Assert;
use WebChemistry\ConsoleExtras\Attribute\Argument;
use WebChemistry\ConsoleExtras\ExtraCommand;

require __DIR__ . '/bootstrap.phpt';

$command = new class extends ExtraCommand {

	#[Argument]
	protected string $required;

	#[Argument]
	protected ?string $optional;

	#[Argument]
	protected string $optionalTwo = 'default';

	#[Argument]
	protected string|int|null $optionalThird;

	protected function exec(InputInterface $input, OutputInterface $output): bool
	{
		$output->write(implode(',', array_map(fn ($value) => var_export($value, true), [
			$this->required,
			$this->optional,
			$this->optionalTwo,
			$this->optionalThird,
		])));

		return true;
	}

};

Assert::same("'required','optional','default','optionalThird'", runConsoleApp($command, [
	'required' => 'required',
	'optional' => 'optional',
	'optionalThird' => 'optionalThird',
]));
Assert::same("'required','optional','default',NULL", runConsoleApp($command, [
	'required' => 'required',
	'optional' => 'optional',
]));
Assert::same("'required','optional','optionalTwo','optionalThird'", runConsoleApp($command, [
	'required' => 'required',
	'optional' => 'optional',
	'optionalTwo' => 'optionalTwo',
	'optionalThird' => 'optionalThird',
]));
Assert::contains('cmd <required> [<optional> [<optionalTwo> [<optionalThird>]]]', runConsoleApp($command, help: true));
