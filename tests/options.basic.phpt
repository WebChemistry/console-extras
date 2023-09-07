<?php declare(strict_types = 1);

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tester\Assert;
use WebChemistry\ConsoleExtras\Attribute\Description;
use WebChemistry\ConsoleExtras\Attribute\Option;
use WebChemistry\ConsoleExtras\ExtraCommand;

require __DIR__ . '/bootstrap.phpt';

$command = new class extends ExtraCommand {

	#[Option]
	#[Description('Basic option')]
	protected bool $basic;

	#[Option]
	protected ?string $require;

	#[Option]
	protected string $optional = 'default';

	#[Option(negatable: true)]
	protected bool $negatable = true;

	#[Option(shortcut: 's')]
	protected ?string $shortcut;

	protected function exec(InputInterface $input, OutputInterface $output): bool
	{
		$output->write(implode(',', array_map(fn ($value) => var_export($value, true), [
			$this->basic,
			$this->require,
			$this->optional,
			$this->negatable,
			$this->shortcut,
		])));

		return true;
	}

};

Assert::contains("false,NULL,'default',true,NULL", runConsoleApp($command));
Assert::contains("true,NULL,'foo',false,'bar'", runConsoleApp($command, [
	'--basic' => null,
	'--optional' => 'foo',
	'--no-negatable' => null,
	'-s' => 'bar',
]));
