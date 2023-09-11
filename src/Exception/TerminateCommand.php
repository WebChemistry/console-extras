<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Exception;

use Throwable;

interface TerminateCommand extends Throwable
{

	public function isSuccess(): bool;

	public function getTerminateCode(): int;

}
