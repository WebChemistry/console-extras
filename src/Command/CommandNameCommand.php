<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Command;

use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use WebChemistry\ConsoleExtras\Attribute\Argument;
use WebChemistry\ConsoleExtras\ExtraCommand;

final class CommandNameCommand extends ExtraCommand
{

	protected static $defaultName = 'command:name';

	#[Argument]
	protected string $className;

	protected function exec(InputInterface $input, OutputInterface $output): void
	{
		$application = $this->getApplication();

		if (!$application) {
			throw new RuntimeException('Application is not set.');
		}

		foreach ($application->all() as $command) {
			if (is_a($command, $this->className, true)) {
				$name = $command->getName();

				if (!$name) {
					throw new RuntimeException(sprintf('Command %s has no name.', $this->className));
				}

				$output->write($name);

				return;
			}
		}
	}

}
