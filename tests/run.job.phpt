<?php declare(strict_types = 1);

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tester\Assert;
use WebChemistry\ConsoleExtras\Attribute\Argument;
use WebChemistry\ConsoleExtras\Command\RunJobsCommand;
use WebChemistry\ConsoleExtras\ExtraCommand;

require __DIR__ . '/bootstrap.phpt';

class FooCommand extends ExtraCommand {

	protected static $defaultName = 'foo';

	#[Argument]
	protected string $required;

	protected function exec(InputInterface $input, OutputInterface $output): void
	{
		$output->write(implode(',', array_map(fn ($value) => var_export($value, true), [
			$this->required,
		])));
	}

};

runConsoleApp(new RunJobsCommand(), [
	'json' => RunJobsCommand::createBuilder()->add(FooCommand::class, [
		'required' => 'required',
	])->build(),
	'--memoryLimit' => 150,
], commands: [
	new FooCommand(),
]);

Assert::same("'required'", runConsoleApp(new RunJobsCommand(), [
	'json' => RunJobsCommand::createBuilder()->add(FooCommand::class, [
		'required' => 'required',
	])->build(),
], commands: [
	new FooCommand(),
]));
Assert::same("Running FooCommand
'required'Running FooCommand
'required2'", runConsoleApp(new RunJobsCommand(), [
	'json' => RunJobsCommand::createBuilder()->add(FooCommand::class, [
		'required' => 'required',
	])->add(FooCommand::class, [
		'required' => 'required2',
	])->build(),
], commands: [
	new FooCommand(),
], autoExit: true));
