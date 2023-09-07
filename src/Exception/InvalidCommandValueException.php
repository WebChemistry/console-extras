<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Exception;

use Exception;

final class InvalidCommandValueException extends Exception
{

	public function __construct(
		public readonly string $name,
		string $message,
	)
	{
		parent::__construct(sprintf('%s: %s', $name, $message));
	}

}
