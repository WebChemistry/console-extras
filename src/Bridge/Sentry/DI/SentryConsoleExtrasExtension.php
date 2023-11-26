<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Bridge\Sentry\DI;

use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\ServiceDefinition;
use ReflectionClass;
use WebChemistry\ConsoleExtras\Attribute\Job;
use WebChemistry\ConsoleExtras\Bridge\Sentry\SentryIntegration;
use WebChemistry\ConsoleExtras\ExtraCommand;

final class SentryConsoleExtrasExtension extends CompilerExtension
{

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('sentry'))
			->setFactory(SentryIntegration::class);
	}

	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		foreach ($builder->findByType(ExtraCommand::class) as $definition) {
			$type = $definition->getType();

			if ($type === null || !class_exists($type)) {
				continue;
			}

			$reflection = new ReflectionClass($type);

			if (!$reflection->getAttributes(Job::class)) {
				continue;
			}

			if ($definition instanceof ServiceDefinition) {
				$definition->addSetup('?->integrate(?)', [$this->prefix('@sentry'), '@self']);
			}
		}
	}

}
