<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Command\Config;

use WebChemistry\ConsoleExtras\Command\CommandJob;

final class KubernetesConfig
{

	/** @var mixed[] */
	private array $container;

	/**
	 * @param string[] $command
	 * @param mixed[] $container
	 */
	public function __construct(
		string $image,
		array $command = [],
		array $container = [],
		private int $backoffLimit = 0,
		private readonly ?string $namespace = null,
		private string $namePattern = '%s',
	)
	{
		$this->container = [
			'name' => 'php',
			'image' => $image,
			'command' => $command,
		];

		$this->container = array_merge($this->container, $container);
	}

	/**
	 * @return mixed[]
	 */
	public function create(CommandJob $job): array
	{
		$config = [
			'apiVersion' => 'batch/v1',
			'kind' => 'CronJob',
			'metadata' => [
				'name' => sprintf($this->namePattern, strtolower(str_replace(':', '-', $job->name))),
			],
			'spec' => [
				'schedule' => $job->schedule,
				'jobTemplate' => $this->createJobTemplate($job->className, $job->arguments, $job->options),
			],
		];

		if ($this->namespace) {
			$config['metadata']['namespace'] = $this->namespace;
		}

		return $config;
	}

	/**
	 * @param mixed[] $arguments
	 * @param mixed[] $options
	 * @return mixed[]
	 */
	public function createJobTemplate(string $className, array $arguments, array $options): array
	{
		$backoffLimit = $options['backoffLimit'] ?? $this->backoffLimit;
		$restartPolicy = $options['restartPolicy'] ?? 'Never';


		$container = $this->container;
		$container['command'][] = 'jobs:run'; // @phpstan-ignore-line
		$container['command'][] = $className; // @phpstan-ignore-line
		$container['command'] = array_merge(
			$container['command'], // @phpstan-ignore-line
			$arguments,
		);

		$this->insertDeepKey($container, 'resources.requests.cpu', $options['cpu'] ?? null);
		$this->insertDeepKey($container, 'resources.requests.memory', $options['memory'] ?? null);
		$this->insertDeepKey($container, 'resources.requests.ephemeral-storage', $options['storage'] ?? null);

		return [
			'spec' => [
				'backoffLimit' => $backoffLimit,
				'template' => [
					'spec' => [
						'restartPolicy' => $restartPolicy,
						'containers' => [
							$container,
						],
					],
				],
			],
		];
	}

	/**
	 * @param mixed[] $container
	 */
	private function insertDeepKey(array &$container, string $string, mixed $param): void
	{
		if ($param === null) {
			return;
		}

		$keys = explode('.', $string);
		$last = array_pop($keys);

		foreach ($keys as $key) {
			if (!isset($container[$key])) { // @phpstan-ignore-line
				$container[$key] = []; // @phpstan-ignore-line
			}

			$container = &$container[$key]; // @phpstan-ignore-line
		}

		$container[$last] = $param; // @phpstan-ignore-line
	}

}
