<?php declare(strict_types = 1);

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tester\Assert;
use WebChemistry\ConsoleExtras\Attribute\Argument;
use WebChemistry\ConsoleExtras\Command\CommandJob;
use WebChemistry\ConsoleExtras\Command\Config\KubernetesConfig;
use WebChemistry\ConsoleExtras\Command\RunJobsCommand;
use WebChemistry\ConsoleExtras\ExtraCommand;
use WebChemistry\ConsoleExtras\Extractor\Serializer\JsonJobSerializer;

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

}

function createJob(string $className, array $arguments = [], string $commandName = 'foo'): CommandJob
{
	return new CommandJob($className, 'void', $commandName, $arguments, '', null, 'slug', []);
}

$serializer = new JsonJobSerializer();

runConsoleApp(new RunJobsCommand(config: new KubernetesConfig('')), [
	'arg' => $serializer->serialize([createJob(FooCommand::class, [
		'required' => 'required',
	])]),
	'--memoryLimit' => 150,
], commands: [
	new FooCommand(),
]);

Assert::same("'required'", runConsoleApp(new RunJobsCommand(config: new KubernetesConfig('')), [
	'arg' => $serializer->serialize([createJob(FooCommand::class, [
		'required' => 'required',
	])]),
], commands: [
	new FooCommand(),
]));
Assert::same("Running FooCommand
'required'Running FooCommand
'required2'", runConsoleApp(new RunJobsCommand(config: new KubernetesConfig('')), [
	'arg' => $serializer->serialize([createJob(FooCommand::class, [
		'required' => 'required',
	]), createJob(FooCommand::class, [
		'required' => 'required2',
	])]),
], commands: [
	new FooCommand(),
], autoExit: true));
