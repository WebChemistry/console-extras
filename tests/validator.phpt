<?php declare(strict_types = 1);

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\ValidatorBuilder;
use Tester\Assert;
use WebChemistry\ConsoleExtras\Attribute\Argument;
use WebChemistry\ConsoleExtras\ExtraCommand;

require __DIR__ . '/bootstrap.phpt';

$command = new class extends ExtraCommand {

	#[Argument]
	#[LessThan(10)]
	protected int $num;

	public function __construct(?string $name = null)
	{
		parent::__construct($name);


		$this->setValidator(
			(new ValidatorBuilder())
				->enableAnnotationMapping()
				->getValidator()
		);
	}

	protected function exec(InputInterface $input, OutputInterface $output): bool
	{
		$output->write(implode(',', array_map(fn ($value) => var_export($value, true), [
			$this->num,
		])));

		return true;
	}

};

Assert::same("num: This value should be less than 10.\n", runConsoleApp($command, [
	'num' => '12',
]));
Assert::same('8', runConsoleApp($command, [
	'num' => '8',
]));
