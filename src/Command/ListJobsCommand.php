<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Command;

use LogicException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use WebChemistry\ConsoleExtras\ExtraCommand;
use WebChemistry\ConsoleExtras\Extractor\CommandJobExtractor;

final class ListJobsCommand extends ExtraCommand
{

	protected static $defaultName = 'jobs:list';

	protected function exec(InputInterface $input, OutputInterface $output): void
	{
		$application = $this->getApplication();

		if (!$application) {
			throw new LogicException('Application is not set.');
		}

		$jobs = (new CommandJobExtractor())->extract($application->all());

		$table = new Table($output);

		$table->setHeaders([
			'Command',
			'Schedule',
		]);
		foreach ($jobs as $job) {
			$table->addRow([
				$job->comment ?: $job->name,
				$job->schedule,
			]);
		}
		
		$table->render();
	}

}
