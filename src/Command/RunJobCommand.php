<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Command;

use LogicException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WebChemistry\ConsoleExtras\Attribute\Argument;
use WebChemistry\ConsoleExtras\ExtraCommand;

final class RunJobCommand extends ExtraCommand
{

	protected static $defaultName = 'jobs:run';

	#[Argument]
	protected string $className;

	protected function exec(InputInterface $input, OutputInterface $output): void
	{
		$application = $this->getApplication();

		if (!$application) {
			throw new LogicException('Application is not set.');
		}

		$this->className = strtr($this->className, '/', '\\');

		if (!class_exists($this->className)) {
			$this->helper->error(sprintf('Class %s does not exist.', $this->className));
		}

		foreach ($application->all() as $command) {
			if (is_a($command, $this->className, true)) {
				$application->run(new ArrayInput([
					'command' => $command->getName(),
				]));

				return;
			}
		}

		$this->helper->error(sprintf('Command %s does not exist.', $this->className));
	}

}
