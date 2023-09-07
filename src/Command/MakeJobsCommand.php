<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Command;

use LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use WebChemistry\ConsoleExtras\Attribute\Description;
use WebChemistry\ConsoleExtras\Attribute\Option;
use WebChemistry\ConsoleExtras\Command\Config\KubernetesConfig;
use WebChemistry\ConsoleExtras\ExtraCommand;
use WebChemistry\ConsoleExtras\Extractor\CommandJobExtractor;

class MakeJobsCommand extends ExtraCommand
{

	protected static $defaultName = 'make:jobs';

	#[Description('Format of output')]
	#[Option]
	protected string $format = 'k8s';

	public function __construct(
		private ?KubernetesConfig $kubernetesConfig = null,
	)
	{
		parent::__construct();
	}

	protected function exec(InputInterface $input, OutputInterface $output): bool
	{
		$application = $this->getApplication();

		if (!$application) {
			throw new LogicException('Application is not set.');
		}

		$jobs = (new CommandJobExtractor())->extract($application->all());

		if ($this->format === 'k8s') {
			$this->kubernetes($jobs, $output);
		}

		return true;
	}

	/**
	 * @param CommandJob[] $jobs
	 */
	protected function kubernetes(array $jobs, OutputInterface $output): void
	{
		$config = $this->kubernetesConfig;

		if (!$config) {
			throw new LogicException('Kubernetes config is not set.');
		}

		$items = [];

		foreach ($jobs as $job) {
			$items[] = $config->create($job);
		}

		$output->writeln(implode("---\n", array_map(
			fn (array $item) => Yaml::dump($item, 10, flags: Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK | Yaml::DUMP_EMPTY_ARRAY_AS_SEQUENCE),
			$items,
		)));
	}

}
