<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Command;

use LogicException;
use Nette\Utils\Json;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WebChemistry\ConsoleExtras\Attribute\Argument;
use WebChemistry\ConsoleExtras\Command\Builder\RunJobBuilder;
use WebChemistry\ConsoleExtras\ExtraCommand;

final class RunJobCommand extends ExtraCommand
{

	protected static $defaultName = 'jobs:run';

	#[Argument]
	protected string $json;

	public static function createBuilder(): RunJobBuilder
	{
		return new RunJobBuilder();
	}

	protected function exec(InputInterface $input, OutputInterface $output): void
	{
		$application = $this->getApplication();

		if (!$application) {
			throw new LogicException('Application is not set.');
		}

		$struct = Json::decode($this->json, Json::FORCE_ARRAY);
		$toRun = [];

		if (!is_array($struct)) {
			throw new LogicException('Json must be an array.');
		}

		foreach ($struct as [$className, $arguments]) {
			$className = strtr($className, '/', '\\');

			if (!class_exists($className)) {
				$this->helper->error(sprintf('Class %s does not exist.', $className));
			}

			foreach ($application->all() as $command) {
				if (is_a($command, $className, true)) {
					$toRun[] = [
						$className,
						new ArrayInput([
							'command' => $command->getName(),
							...$arguments,
						]),
					];

					continue 2;
				}
			}

			$this->helper->error(sprintf('Command %s does not exist.', $className));
		}

		$printName = count($toRun) > 1;

		foreach ($toRun as [$className, $input]) {
			if ($printName) {
				$this->helper->comment(sprintf('Running %s', $className));
			}

			$application->run($input, $output);
		}
	}

}
