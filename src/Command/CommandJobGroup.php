<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Command;

use LogicException;
use Nette\Utils\Arrays;
use Symfony\Component\Console\Command\Command;

final class CommandJobGroup
{

	/** @var CommandJob[] */
	public array $jobs = [];

	/** @var string[] */
	private array $descriptions = [];

	/**
	 * @param mixed[] $options
	 */
	public function __construct(
		public readonly string $schedule,
		public readonly array $options = [],
		public readonly string $name = '',
	)
	{
	}

	public function getJobName(): string
	{
		if ($this->name) {
			return $this->name;
		}

		$job = Arrays::first($this->jobs);

		if (!$job) {
			throw new LogicException('No job found.');
		}

		return $job->name;
	}

	public function addJob(CommandJob $job, Command $command, string $description): self
	{
		$description = trim($description);

		$this->jobs[] = $job;

		if ($description) {
			$this->descriptions[] = sprintf('%s - %s', $command::class, $description);
		} else {
			$this->descriptions[] = $command::class;
		}

		return $this;
	}

	public function getComment(string $lineSeparator = '# '): string
	{
		if (!$this->descriptions) {
			return '';
		}

		return $lineSeparator . implode("\n" . $lineSeparator, $this->descriptions);
	}

}
