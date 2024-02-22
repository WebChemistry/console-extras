<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Command\Config;

use WebChemistry\ConsoleExtras\Command\CommandJobGroup;
use WebChemistry\ConsoleExtras\Extractor\Serializer\JobSerializer;
use WebChemistry\ConsoleExtras\Extractor\Serializer\JsonJobSerializer;

/**
 * @phpstan-type KubernetesConfigOptions array{
 *      concurrencyPolicy?: 'Allow' | 'Forbid' | 'Replace',
 *      backoffLimit?: int,
 *      restartPolicy?: 'Never' | 'OnFailure',
 *      successfulJobsHistoryLimit?: int,
 *      failedJobsHistoryLimit?: int,
 *      timeZone?: string,
 *      ttlSecondsAfterFinished?: int,
 * }
 */
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

	public function getSerializer(): JobSerializer
	{
		return new JsonJobSerializer();
	}

	/**
	 * @param CommandJobGroup $group
	 * @return KubernetesConfigOptions
	 */
	private function getOptions(CommandJobGroup $group): array
	{
		return $group->options; // @phpstan-ignore-line
	}

	/**
	 * @return mixed[]
	 */
	public function create(CommandJobGroup $group): array
	{
		$options = $this->getOptions($group);

		$config = [
			'apiVersion' => 'batch/v1',
			'kind' => 'CronJob',
			'metadata' => [
				'name' => sprintf($this->namePattern, strtolower(str_replace(':', '-', $group->getJobName()))),
			],
			'spec' => [
				'schedule' => $group->schedule,
				'jobTemplate' => $this->createJobTemplate($group),
			],
		];

		$this->insertDeepKey($config, 'spec.successfulJobsHistoryLimit', $options['successfulJobsHistoryLimit'] ?? null);
		$this->insertDeepKey($config, 'spec.failedJobsHistoryLimit', $options['failedJobsHistoryLimit'] ?? null);
		$this->insertDeepKey($config, 'spec.concurrencyPolicy', $options['concurrencyPolicy'] ?? null);
		$this->insertDeepKey($config, 'spec.timeZone', $options['timeZone'] ?? null);
		$this->insertDeepKey($config, 'spec.jobTemplate.spec.ttlSecondsAfterFinished', $options['ttlSecondsAfterFinished'] ?? null);

		if ($this->namespace) {
			$config['metadata']['namespace'] = $this->namespace;
		}

		return $config;
	}

	/**
	 * @return mixed[]
	 */
	private function createJobTemplate(CommandJobGroup $group): array
	{
		$options = $group->options;
		$serializer = $this->getSerializer();

		$backoffLimit = $options['backoffLimit'] ?? $this->backoffLimit;
		$restartPolicy = $options['restartPolicy'] ?? 'Never';

		$container = $this->container;
		$container['command'][] = 'jobs:run'; // @phpstan-ignore-line
		$container['command'][] = $serializer->serialize($group->jobs); // @phpstan-ignore-line

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
	 * @param mixed[] $config
	 */
	private function insertDeepKey(array &$config, string $string, mixed $param): void
	{
		if ($param === null) {
			return;
		}

		$keys = explode('.', $string);
		$last = array_pop($keys);

		foreach ($keys as $key) {
			if (!isset($config[$key])) { // @phpstan-ignore-line
				$config[$key] = []; // @phpstan-ignore-line
			}

			$config = &$config[$key]; // @phpstan-ignore-line
		}

		$config[$last] = $param; // @phpstan-ignore-line
	}

}
