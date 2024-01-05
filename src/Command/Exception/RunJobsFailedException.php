<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Command\Exception;

use Exception;
use Throwable;

final class RunJobsFailedException extends Exception
{

	/**
	 * @param Throwable[] $exceptions
	 */
	public function __construct(
		public readonly array $exceptions,
	)
	{
		parent::__construct(implode("\n", array_map(fn (Throwable $e) => $e->getMessage(), $this->exceptions)));
	}

}
