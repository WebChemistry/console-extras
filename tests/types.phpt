<?php declare(strict_types = 1);

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tester\Assert;
use WebChemistry\ConsoleExtras\Attribute\Option;
use WebChemistry\ConsoleExtras\ExtraCommand;

require __DIR__ . '/bootstrap.phpt';

$command = new class extends ExtraCommand {

	#[Option]
	protected ?DateTime $start;

	protected function exec(InputInterface $input, OutputInterface $output): bool
	{
		$output->write(implode(',', array_map(fn ($value) => var_export($value, true), [
			$this->start->format('Y-m-d H:i:s'),
		])));

		return true;
	}

};

Assert::same("'2022-01-01 00:00:00'", runConsoleApp($command, [
	'--start' => '2022-01-01',
]));

Assert::same("'2022-01-01 12:12:12'", runConsoleApp($command, [
	'--start' => '2022-01-01 12:12:12',
]));
