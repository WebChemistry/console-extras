<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Attribute;

use Attribute;
use BackedEnum;
use WebChemistry\ConsoleExtras\Group\GroupedSchedule;

#[Attribute(Attribute::TARGET_CLASS)]
final class Job
{

	/**
	 * @param string|GroupedSchedule|BackedEnum $schedule minute hour day month dayOfWeek
	 * @param mixed[] $arguments
	 * @param mixed[] $options
	 */
	public function __construct(
		public readonly string|GroupedSchedule|BackedEnum $schedule,
		public readonly array $arguments = [],
		public readonly array $options = [],
		public readonly ?string $slug = null,
	)
	{
	}

}
