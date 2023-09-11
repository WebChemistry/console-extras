<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Exception;

use Exception;
use LogicException;
use Symfony\Component\Console\Command\Command;

final class ErroneouslyTerminateCommand extends Exception implements TerminateCommand
{

	public function __construct(
		private readonly int $terminateCode = Command::FAILURE,
	)
	{
		if ($this->terminateCode === Command::SUCCESS) {
			throw new LogicException('Command was terminated successfully, use SuccessfullyTerminateCommand instead.');
		}

		parent::__construct('Command was terminated with error.');
	}

	public function isSuccess(): bool
	{
		return false;
	}

	public function getTerminateCode(): int
	{
		return $this->terminateCode;
	}

}
