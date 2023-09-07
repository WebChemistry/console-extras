<?php declare(strict_types = 1);

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Tester\Assert;
use WebChemistry\ConsoleExtras\Command\CommandNameCommand;
use WebChemistry\ConsoleExtras\ExtraCommand;

require __DIR__ . '/bootstrap.phpt';

final class TestCaseCommand extends ExtraCommand
{

	protected static $defaultName = 'foo';

	protected function exec(InputInterface $input, OutputInterface $output): bool
	{
		return true;
	}

}

$application = new Application();
$application->add(new CommandNameCommand());
$application->add(new TestCaseCommand());
$application->setAutoExit(false);

$run = function (array $input) use ($application): string {
	$application->run(
		new ArrayInput($input),
		$output = new BufferedOutput(),
	);

	return $output->fetch();
};

Assert::same('foo', $run([
	'command' => 'command:name',
	'className' => TestCaseCommand::class,
]));
