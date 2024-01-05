<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Bridge\Nette\DI;

use Nette\DI\CompilerExtension;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use stdClass;
use WebChemistry\ConsoleExtras\Command\Config\KubernetesConfig;
use WebChemistry\ConsoleExtras\Command\ListJobsCommand;
use WebChemistry\ConsoleExtras\Command\MakeJobsCommand;
use WebChemistry\ConsoleExtras\Command\RunJobsCommand;

final class ConsoleExtrasExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'jobs' => Expect::structure([
				'environment' => Expect::structure([
					'kubernetes' => Expect::structure([
						'image' => Expect::string()->required(),
						'command' => Expect::listOf(Expect::scalar())->required(),
						'container' => Expect::arrayOf(Expect::mixed()),
						'backoffLimit' => Expect::int(0),
						'namespace' => Expect::string(),
						'namePattern' => Expect::string('%s'),
					])->required(false),
				])->required(),
			])->required(false),
		]);
	}

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		/** @var stdClass $config */
		$config = $this->getConfig();

		if ($config->jobs !== null) {
			$builder->addDefinition($this->prefix('jobs.commands.run'))
				->setFactory(RunJobsCommand::class);

			$builder->addDefinition($this->prefix('jobs.commands.list'))
				->setFactory(ListJobsCommand::class);
		}

		$this->configureJobEnvironemnt($config->jobs?->environment);
	}

	private function configureJobEnvironemnt(?stdClass $environment): void
	{
		if (!$environment) {
			return;
		}

		$builder = $this->getContainerBuilder();

		if ($kubernetes = $environment->kubernetes) {
			$config = $builder->addDefinition($this->prefix('jobs.environment.config'))
				->setFactory(KubernetesConfig::class, [
					$kubernetes->image,
					$kubernetes->command,
					$kubernetes->container,
					$kubernetes->backoffLimit,
					$kubernetes->namespace,
					$kubernetes->namePattern,
				]);
		}

		if (isset($config)) {
			$builder->addDefinition($this->prefix('jobs.commands.make'))
				->setFactory(MakeJobsCommand::class, [$config]);
		}
	}

}
