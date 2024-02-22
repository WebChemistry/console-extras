<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Extractor\Serializer;

use WebChemistry\ConsoleExtras\Command\CommandJob;
use WebChemistry\ConsoleExtras\Command\CommandJobGroup;

interface JobSerializer
{

	/**
	 * @param CommandJob[] $jobs
	 */
	public function serialize(array $jobs): string;

	/**
	 * @return UnserializedJob[]
	 */
	public function unserialize(string $data): array;

}
