<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Extractor\Serializer;

use InvalidArgumentException;
use Nette\Utils\Json;
use WebChemistry\ConsoleExtras\Command\CommandJob;

final class JsonJobSerializer implements JobSerializer
{

	/**
	 * @param CommandJob[] $jobs
	 */
	public function serialize(array $jobs): string
	{
		$commands = [];

		foreach ($jobs as $job) {
			$commands[] = [$job->className, $job->commandName, $job->arguments];
		}

		return Json::encode($commands);
	}

	/**
	 * @return UnserializedJob[]
	 */
	public function unserialize(string $data): array
	{
		$commands = Json::decode($data, Json::FORCE_ARRAY);

		if (!is_array($commands)) {
			throw new InvalidArgumentException('Invalid command structure.');
		}

		$jobs = [];

		foreach ($commands as $command) {
			if (!is_array($command)) {
				throw new InvalidArgumentException('Invalid command structure.');
			}

			$commandName = null;

			if (count($command) === 3) {
				[$className, $commandName, $arguments] = $command;

				assert(is_string($className));
				assert(is_string($commandName));
				assert(is_array($arguments));

			} else if (count($command) === 2) {
				[$className, $arguments] = $command;

				assert(is_string($className));
				assert(is_array($arguments));

			} else {
				throw new InvalidArgumentException('Invalid command structure.');
			}

			$jobs[] = new UnserializedJob($className, $commandName, $arguments);
		}

		return $jobs;
	}

}
