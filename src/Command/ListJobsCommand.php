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

		$groups = (new CommandJobExtractor())->extractAll($application->all());

		$table = new Table($output);

		$table->setHeaders([
			'Command',
			'Description',
			'Schedule',
			'Group',
		]);

		ksort($groups);

		foreach ($groups as $group) {
			foreach ($group->jobs as $job) {
				$table->addRow([
					$job->className,
					$job->comment,
					$job->schedule,
					$group->name,
				]);
			}
		}

		$table->render();
	}

}
