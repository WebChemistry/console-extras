<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Attribute;

use Attribute;
use BackedEnum;

#[Attribute(Attribute::TARGET_CLASS)]
final class Job
{

	/**
	 * @param mixed[] $arguments
	 * @param mixed[] $options
	 */
	public function __construct(
		public readonly string|BackedEnum $schedule,
		public readonly array $arguments = [],
		public readonly array $options = [],
		public readonly ?string $name = null,
	)
	{
	}

}
