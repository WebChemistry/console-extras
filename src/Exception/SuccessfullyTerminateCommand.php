<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Exception;

use Exception;
use Symfony\Component\Console\Command\Command;
use Throwable;

final class SuccessfullyTerminateCommand extends Exception implements TerminateCommand
{

	public function __construct(string $message = '', int $code = 0, ?Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

	public function isSuccess(): bool
	{
		return true;
	}

	public function getTerminateCode(): int
	{
		return Command::SUCCESS;
	}

}
