<?php declare(strict_types = 1);

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tester\Assert;
use WebChemistry\ConsoleExtras\Attribute\Argument;
use WebChemistry\ConsoleExtras\ExtraCommand;

require __DIR__ . '/bootstrap.phpt';

$command = new class extends ExtraCommand {

	#[Argument]
	protected bool $bool;

	protected function exec(InputInterface $input, OutputInterface $output): bool
	{
		$output->write(implode(',', array_map(fn ($value) => var_export($value, true), [
			$this->bool,
		])));

		return true;
	}

};

Assert::same('true', runConsoleApp($command, [
	'bool' => '1',
]));
Assert::same('true', runConsoleApp($command, [
	'bool' => 'true',
]));

Assert::same('false', runConsoleApp($command, [
	'bool' => '0',
]));
Assert::same('false', runConsoleApp($command, [
	'bool' => 'false',
]));
