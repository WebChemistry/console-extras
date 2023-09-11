<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Exception;

use Exception;
use Symfony\Component\Console\Command\Command;

final class TerminateCommandException extends Exception implements TerminateCommand
{

	public function __construct(
		private readonly int $terminateCode = Command::SUCCESS,
	)
	{
		parent::__construct($this->isSuccess() ? 'Command was terminated successfully.' : 'Command was terminated with error.');
	}

	public function isSuccess(): bool
	{
		return $this->terminateCode === Command::SUCCESS;
	}

	public function getTerminateCode(): int
	{
		return $this->terminateCode;
	}

}
