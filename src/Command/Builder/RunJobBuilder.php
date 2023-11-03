<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Command\Builder;

use Nette\Utils\Json;

final class RunJobBuilder
{

	/** @var array{string, mixed[]}[] */
	private array $commands;

	/**
	 * @param class-string $className
	 * @param mixed[] $arguments
	 */
	public function add(string $className, array $arguments = []): self
	{
		$this->commands[] = [$className, $arguments];

		return $this;
	}

	public function build(): string
	{
		return Json::encode($this->commands);
	}

}
