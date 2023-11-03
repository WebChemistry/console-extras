<?php declare(strict_types = 1);

namespace WebChemistry\ConsoleExtras\Group;

interface GroupedSchedule
{

	public function getGroupName(): string;

	public function getSchedule(): string;

	/** @return mixed[] */
	public function getOptions(): array;

}
