<?php declare(strict_types = 1);

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tester\Environment;

require __DIR__ . '/../vendor/autoload.php';

/**
 * @param array<string, string> $arguments
 */
function runConsoleApp(Command $command, array $arguments = [], bool $help = false, string $name = 'cmd'): string
{
	$command->setName($name);

	if ($help) {
		$arguments['--help'] = '';
	}

	$arguments = [
		'command' => $name,
		... $arguments,
	];

	$app = new Application();
	$app->setAutoExit(false);
	$app->add($command);
	$app->run(
		new ArrayInput($arguments),
		$output = new BufferedOutput(),
	);

	return $output->fetch();
}

Environment::setup();
