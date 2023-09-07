<?php declare(strict_types = 1);

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\ValidatorBuilder;
use Tester\Assert;
use WebChemistry\ConsoleExtras\Attribute\Argument;
use WebChemistry\ConsoleExtras\ExtraCommand;

require __DIR__ . '/bootstrap.phpt';

enum TestCaseEnum: string {
	case A = 'a';
	case B = 'b';

}

$command = new class extends ExtraCommand {

	#[Argument]
	protected TestCaseEnum $enum;

	protected function exec(InputInterface $input, OutputInterface $output): bool
	{
		$output->write(implode(',', array_map(fn ($value) => var_export($value, true), [
			$this->enum->name,
		])));

		return true;
	}

};

Assert::same("enum: Value must be one of \"a, b\", c given.\n", runConsoleApp($command, [
	'enum' => 'c',
]));

Assert::same("'A'", runConsoleApp($command, [
	'enum' => 'a',
]));
