<?php declare(strict_types = 1);

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tester\Assert;
use WebChemistry\ConsoleExtras\Attribute\Argument;
use WebChemistry\ConsoleExtras\ExtraCommand;

require __DIR__ . '/bootstrap.phpt';

$command = new class extends ExtraCommand {

	#[Argument]
	protected int|float $num;

	protected function exec(InputInterface $input, OutputInterface $output): bool
	{
		$output->write(implode(',', array_map(fn ($value) => var_export($value, true), [
			$this->num,
		])));

		return true;
	}

};

Assert::same('12', runConsoleApp($command, [
	'num' => '12',
]));

Assert::same('12.6', runConsoleApp($command, [
	'num' => '12.6',
]));

Assert::same("num: Cannot convert value to int|float.\n", runConsoleApp($command, [
	'num' => 'foo',
]));
