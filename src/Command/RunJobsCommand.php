<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Command;

use LogicException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;
use WebChemistry\ConsoleExtras\Attribute\Argument;
use WebChemistry\ConsoleExtras\Command\Config\KubernetesConfig;
use WebChemistry\ConsoleExtras\Command\Exception\RunJobsFailedException;
use WebChemistry\ConsoleExtras\ExtraCommand;

final class RunJobsCommand extends ExtraCommand
{

	protected static $defaultName = 'jobs:run';

	#[Argument]
	protected string $arg;

	public function __construct(
		?string $name = null,
		private bool $printErrors = true,
		private ?KubernetesConfig $config = null,
	)
	{
		parent::__construct($name);
	}

	protected function exec(InputInterface $input, OutputInterface $output): void
	{
		$application = $this->getApplication();
		$config = $this->config;

		if (!$application) {
			throw new LogicException('Application is not set.');
		}

		if (!$config) {
			throw new LogicException('Config is not set.');
		}

		$jobs = $config->getSerializer()->unserialize($this->arg);
		$toRun = [];

		foreach ($jobs as $job) {
			$className = strtr($job->className, '/', '\\');

			if (class_exists($className)) {
				foreach ($application->all() as $command) {
					if (is_a($command, $className, true)) {
						$toRun[] = [
							$className,
							$command->getName(),
							new ArrayInput([
								'command' => $command->getName(),
								...$job->arguments,
							]),
						];

						continue 2;
					}
				}
			}

			foreach ($application->all() as $command) {
				if ($command->getName() === $job->commandName) {
					$toRun[] = [
						$className,
						$command->getName(),
						new ArrayInput([
							'command' => $command->getName(),
							...$job->arguments,
						]),
					];

					continue 2;
				}
			}

			$this->helper->error(sprintf('Command with class name %s or command name "%s" does not exist.', $className, $job->commandName));
		}

		$printName = count($toRun) > 1;

		$autoExit = $application->isAutoExitEnabled();
		$catchExceptions = $application->areExceptionsCaught();

		$application->setAutoExit(false);
		$application->setCatchExceptions(false);

		$success = true;

		foreach ($toRun as [$className, $commandName, $input]) {
			if ($printName) {
				$this->helper->comment(sprintf('Running %s (%s)', $className, $commandName));
			}

			try {
				$code = $application->run($input, $output);

				if ($code !== 0) {
					$success = false;
				}

			} catch (Throwable $exception) {
				if ($this->printErrors) {
					$this->helper->error(sprintf('%s failed: %s', $commandName, $exception->getMessage()), false);

					foreach ($exception->getTrace() as $trace) {
						if (isset($trace['file']) && isset($trace['line'])) {
							$this->helper->error(sprintf(' in %s:%s', $trace['file'], $trace['line']), false);
						}
					}
				}
			}
		}

		$application->setAutoExit($autoExit);
		$application->setCatchExceptions($catchExceptions);

		if (!$success) {
			$this->helper->terminate(false);
		}

		$this->helper->terminateIfErrors();
	}

}
